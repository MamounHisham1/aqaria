<?php

use App\Models\Listing;
use App\Models\TelegramMessage;
use App\Models\TelegramUser;
use App\Services\Telegram\AiAssistantService;
use App\Services\Telegram\AuthorizationService;
use App\Services\Telegram\Contracts\ChatClient;
use App\Services\Telegram\ConversationContext;
use App\Services\Telegram\ListingToolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.telegram.bot_token', 'test-token');
    config()->set('services.telegram.webhook_secret', 'test-secret');
});

// ========== Webhook /start ==========

test('webhook handles start command for new user and logs message', function () {
    Http::fake();

    $payload = [
        'message' => [
            'message_id' => 42,
            'chat' => ['id' => 123456],
            'from' => [
                'id' => 123456,
                'first_name' => 'John',
                'username' => 'john_doe',
            ],
            'text' => '/start',
        ],
    ];

    $response = $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    expect(TelegramUser::where('telegram_id', '123456')->exists())->toBeTrue();

    $message = TelegramMessage::query()
        ->where('telegram_chat_id', '123456')
        ->where('telegram_message_id', '42')
        ->first();

    expect($message)->not->toBeNull();
    expect($message->text)->toBe('/start');
    expect($message->raw_payload)->toBe($payload);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'sendMessage')
            && str_contains($request['text'], 'Hello John');
    });
});

// ========== Webhook /clear ==========

test('webhook clears conversation context', function () {
    Http::fake();

    $chatId = '123456';
    $context = app(ConversationContext::class);
    $context->addMessage($chatId, 'user', 'previous message');

    $payload = [
        'message' => [
            'chat' => ['id' => (int) $chatId],
            'from' => [
                'id' => (int) $chatId,
                'first_name' => 'John',
            ],
            'text' => '/clear',
        ],
    ];

    $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ])->assertOk();

    expect($context->getMessages($chatId))->toBeEmpty();
});

// ========== AI Message Processing ==========

test('webhook processes message through ai and sends response', function () {
    $mockClient = new class implements ChatClient
    {
        public function create(array $params): array
        {
            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => 'I found 3 active listings in Cairo.',
                            'tool_calls' => null,
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
            ];
        }
    };

    app()->bind(ChatClient::class, fn () => $mockClient);

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    TelegramUser::factory()->create(['telegram_id' => '123456']);

    $payload = [
        'message' => [
            'chat' => ['id' => 123456],
            'from' => [
                'id' => 123456,
                'first_name' => 'John',
            ],
            'text' => 'Show me listings in Cairo',
        ],
    ];

    $response = $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'sendMessage')
            && $request['text'] === 'I found 3 active listings in Cairo.';
    });
});

test('webhook executes tool calls when ai requests them', function () {
    Listing::factory()->create([
        'title' => 'Test Villa',
        'city' => 'Cairo',
        'is_active' => true,
        'price' => 5000000,
    ]);

    $mockClient = new class implements ChatClient
    {
        private int $count = 0;

        public function create(array $params): array
        {
            $this->count++;
            if ($this->count === 1) {
                return [
                    'choices' => [
                        [
                            'message' => [
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_123',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'search_listings',
                                            'arguments' => json_encode(['city' => 'Cairo']),
                                        ],
                                    ],
                                ],
                            ],
                            'finish_reason' => 'tool_calls',
                        ],
                    ],
                ];
            }

            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Here are the Cairo listings.',
                            'tool_calls' => null,
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
            ];
        }
    };

    app()->bind(ChatClient::class, fn () => $mockClient);

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    TelegramUser::factory()->create(['telegram_id' => '123456']);

    $payload = [
        'message' => [
            'chat' => ['id' => 123456],
            'from' => [
                'id' => 123456,
                'first_name' => 'John',
            ],
            'text' => 'Find listings in Cairo',
        ],
    ];

    $response = $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'sendMessage');
    });
});

// ========== Authorization ==========

test('unauthorized user cannot trigger create_listing tool', function () {
    $toolService = new ListingToolService;

    $result = $toolService->createListing([
        'title' => 'Test',
        'description' => 'Desc',
        'price' => 1000000,
        'area_sqm' => 150,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'property_type' => 'apartment',
        'listing_type' => 'sale',
        'city' => 'Cairo',
        'district' => 'Maadi',
        'address' => '123 Street',
        'contact_phone' => '01234567890',
    ]);

    expect($result['success'])->toBeTrue();
    expect(Listing::count())->toBe(1);
});

test('openai service restricts modify tools for non-admin users', function () {
    $user = TelegramUser::factory()->create(['is_authorized' => false, 'role' => 'user']);
    $toolService = new ListingToolService;
    $context = new ConversationContext;
    $mockClient = Mockery::mock(ChatClient::class);
    $service = new AiAssistantService($toolService, $context, $mockClient);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getTools');
    $tools = $method->invoke($service, $user);

    $toolNames = array_column(array_column($tools, 'function'), 'name');

    expect($toolNames)->toContain('search_listings');
    expect($toolNames)->not->toContain('create_listing');
    expect($toolNames)->not->toContain('update_listing');
    expect($toolNames)->not->toContain('delete_listing');
});

test('openai service includes modify tools for admin users', function () {
    $user = TelegramUser::factory()->authorized()->create();
    $toolService = new ListingToolService;
    $context = new ConversationContext;
    $mockClient = Mockery::mock(ChatClient::class);
    $service = new AiAssistantService($toolService, $context, $mockClient);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getTools');
    $tools = $method->invoke($service, $user);

    $toolNames = array_column(array_column($tools, 'function'), 'name');

    expect($toolNames)->toContain('search_listings');
    expect($toolNames)->toContain('create_listing');
    expect($toolNames)->toContain('update_listing');
    expect($toolNames)->toContain('delete_listing');
});

// ========== ListingToolService ==========

test('search_listings filters by city', function () {
    Listing::factory()->count(3)->create(['city' => 'Cairo', 'is_active' => true]);
    Listing::factory()->count(2)->create(['city' => 'Alexandria', 'is_active' => true]);

    $service = new ListingToolService;
    $result = $service->searchListings(['city' => 'Cairo']);

    expect($result['count'])->toBe(3);
});

test('search_listings respects is_active filter', function () {
    Listing::factory()->count(2)->create(['is_active' => true]);
    Listing::factory()->count(3)->create(['is_active' => false]);

    $service = new ListingToolService;
    $result = $service->searchListings([]);

    expect($result['count'])->toBe(2);
});

test('create_listing validates required fields', function () {
    $service = new ListingToolService;
    $result = $service->createListing(['title' => 'Only Title']);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Validation failed');
});

test('update_listing modifies existing listing', function () {
    $listing = Listing::factory()->create(['price' => 1000000, 'title' => 'Old Title']);

    $service = new ListingToolService;
    $result = $service->updateListing([
        'id' => $listing->id,
        'price' => 2200000,
    ]);

    expect($result['success'])->toBeTrue();
    expect((float) $listing->fresh()->price)->toEqual(2200000.00);
});

test('update_listing returns error for missing id', function () {
    $service = new ListingToolService;
    $result = $service->updateListing(['title' => 'New Title']);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Listing ID is required.');
});

test('delete_listing removes listing', function () {
    $listing = Listing::factory()->create();

    $service = new ListingToolService;
    $result = $service->deleteListing($listing->id);

    expect($result['success'])->toBeTrue();
    expect(Listing::find($listing->id))->toBeNull();
});

test('delete_listing returns error for missing listing', function () {
    $service = new ListingToolService;
    $result = $service->deleteListing(99999);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('not found');
});

// ========== ConversationContext ==========

test('conversation context stores and retrieves messages', function () {
    $context = new ConversationContext;
    $chatId = 'chat_123';

    $context->addMessage($chatId, 'user', 'Hello');
    $context->addMessage($chatId, 'assistant', 'Hi there');

    $messages = $context->getMessages($chatId);

    expect($messages)->toHaveCount(2);
    expect($messages[0])->toBe(['role' => 'user', 'content' => 'Hello']);
    expect($messages[1])->toBe(['role' => 'assistant', 'content' => 'Hi there']);
});

test('conversation context limits message history', function () {
    $context = new ConversationContext;
    $chatId = 'chat_456';

    for ($i = 0; $i < 25; $i++) {
        $context->addMessage($chatId, 'user', "Message {$i}");
    }

    $messages = $context->getMessages($chatId);

    expect($messages)->toHaveCount(20);
});

test('conversation context tracks last listing id', function () {
    $context = new ConversationContext;
    $chatId = 'chat_789';

    $context->setLastListingId($chatId, 42);

    expect($context->getLastListingId($chatId))->toBe(42);
});

test('webhook ignores non-message updates', function () {
    $response = $this->postJson(route('telegram.webhook'), ['update_id' => 1], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();
    expect($response->json('status'))->toBe('ignored');
});

// ========== AuthorizationService ==========

test('authorization service creates new telegram user', function () {
    $service = new AuthorizationService;

    $user = $service->findOrCreateUser([
        'id' => 999999,
        'first_name' => 'Alice',
        'username' => 'alice_tg',
    ]);

    expect($user->telegram_id)->toBe('999999');
    expect($user->first_name)->toBe('Alice');
    expect($user->is_authorized)->toBeFalse();
});

test('authorization service updates existing user on re-entry', function () {
    TelegramUser::factory()->create([
        'telegram_id' => '888888',
        'first_name' => 'Bob',
        'username' => 'old_name',
    ]);

    $service = new AuthorizationService;

    $user = $service->findOrCreateUser([
        'id' => 888888,
        'first_name' => 'Robert',
        'username' => 'new_name',
    ]);

    expect($user->first_name)->toBe('Robert');
    expect($user->username)->toBe('new_name');
});
