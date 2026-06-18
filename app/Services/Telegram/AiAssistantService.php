<?php

namespace App\Services\Telegram;

use App\Models\TelegramUser;
use App\Services\Telegram\Contracts\ChatClient;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private const SYSTEM_PROMPT = 'You are a helpful real estate assistant for a Laravel application. You manage property listings through function calls. Always confirm destructive actions with the user before executing. When the user refers to "it" or "this listing" without specifying an ID, use the context of the last discussed listing. Respond concisely and professionally.';

    public function __construct(
        private ListingToolService $listingTools,
        private ConversationContext $context,
        private ChatClient $chatClient,
    ) {}

    public function processMessage(string $chatId, string $message, TelegramUser $user): string
    {
        $this->context->addMessage($chatId, 'user', $message);
        $messages = $this->buildMessages($chatId);

        try {
            $response = $this->chatClient->create([
                'messages' => $messages,
                'tools' => $this->getTools($user),
            ]);
        } catch (\Exception $e) {
            Log::error('AI request failed', ['error' => $e->getMessage()]);

            return 'Sorry, I encountered an error processing your request. Please try again later.';
        }

        $choice = $response['choices'][0] ?? null;

        if ($choice === null) {
            return 'Sorry, I did not receive a valid response. Please try again.';
        }

        if (! empty($choice['message']['tool_calls'])) {
            $this->context->addAssistantMessageWithTool(
                $chatId,
                $choice['message']['content'] ?? '',
                $this->serializeToolCalls($choice['message']['tool_calls']),
            );

            $this->executeToolCalls($choice['message']['tool_calls'], $chatId, $user);

            $messages = $this->buildMessages($chatId);
            $secondResponse = $this->chatClient->create([
                'messages' => $messages,
                'tools' => $this->getTools($user),
            ]);

            $finalContent = $secondResponse['choices'][0]['message']['content'] ?? 'Done.';
            $this->context->addMessage($chatId, 'assistant', $finalContent);

            return $finalContent;
        }

        $content = $choice['message']['content'] ?? 'I\'m not sure how to help with that.';
        $this->context->addMessage($chatId, 'assistant', $content);

        return $content;
    }

    private function buildMessages(string $chatId): array
    {
        $messages = [['role' => 'system', 'content' => self::SYSTEM_PROMPT]];
        $stored = $this->context->getMessages($chatId);

        foreach ($stored as $msg) {
            $message = ['role' => $msg['role'], 'content' => $msg['content'] ?? ''];

            if (! empty($msg['tool_calls'])) {
                $message['tool_calls'] = $msg['tool_calls'];
            }

            if (! empty($msg['tool_call_id'])) {
                $message['tool_call_id'] = $msg['tool_call_id'];
            }

            $messages[] = $message;
        }

        return $messages;
    }

    private function getTools(TelegramUser $user): array
    {
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_listings',
                    'description' => 'Search and retrieve property listings based on user criteria.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'city' => ['type' => 'string', 'description' => 'City name filter'],
                            'listing_type' => ['type' => 'string', 'enum' => ['sale', 'rent']],
                            'property_type' => ['type' => 'string', 'enum' => ['apartment', 'villa', 'townhouse', 'commercial']],
                            'search_term' => ['type' => 'string', 'description' => 'Generic search term for title/description'],
                            'min_price' => ['type' => 'number'],
                            'max_price' => ['type' => 'number'],
                            'min_bedrooms' => ['type' => 'integer'],
                            'is_active' => ['type' => 'boolean', 'description' => 'Filter by active status. Defaults to true.'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_listing_by_id',
                    'description' => 'Retrieve a specific listing by its ID. Use this when the user references a listing number.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'The listing ID'],
                        ],
                        'required' => ['id'],
                    ],
                ],
            ],
        ];

        if ($user->isAdmin()) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'create_listing',
                    'description' => 'Create a new property listing.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Listing title'],
                            'description' => ['type' => 'string', 'description' => 'Property description'],
                            'price' => ['type' => 'number', 'description' => 'Price in EGP'],
                            'area_sqm' => ['type' => 'integer', 'description' => 'Area in square meters'],
                            'bedrooms' => ['type' => 'integer'],
                            'bathrooms' => ['type' => 'integer'],
                            'property_type' => ['type' => 'string', 'enum' => ['apartment', 'villa', 'townhouse', 'commercial']],
                            'listing_type' => ['type' => 'string', 'enum' => ['sale', 'rent']],
                            'city' => ['type' => 'string'],
                            'district' => ['type' => 'string'],
                            'address' => ['type' => 'string'],
                        ],
                        'required' => ['title', 'description', 'price', 'area_sqm', 'bedrooms', 'bathrooms', 'property_type', 'listing_type', 'city', 'district', 'address'],
                    ],
                ],
            ];

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'update_listing',
                    'description' => 'Update an existing listing by ID. Only include fields that need to change.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'The listing ID to update'],
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'price' => ['type' => 'number'],
                            'area_sqm' => ['type' => 'integer'],
                            'bedrooms' => ['type' => 'integer'],
                            'bathrooms' => ['type' => 'integer'],
                            'property_type' => ['type' => 'string', 'enum' => ['apartment', 'villa', 'townhouse', 'commercial']],
                            'listing_type' => ['type' => 'string', 'enum' => ['sale', 'rent']],
                            'city' => ['type' => 'string'],
                            'district' => ['type' => 'string'],
                            'address' => ['type' => 'string'],
                            'is_active' => ['type' => 'boolean'],
                            'is_featured' => ['type' => 'boolean'],
                        ],
                        'required' => ['id'],
                    ],
                ],
            ];

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'delete_listing',
                    'description' => 'Delete a listing by ID. Always ask for confirmation before using this.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'The listing ID to delete'],
                        ],
                        'required' => ['id'],
                    ],
                ],
            ];
        }

        return $tools;
    }

    private function executeToolCalls(array $toolCalls, string $chatId, TelegramUser $user): array
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            $name = $toolCall['function']['name'] ?? '';
            $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];
            $result = $this->executeTool($name, $arguments, $user);

            $this->context->addToolResult($chatId, $toolCall['id'] ?? '', json_encode($result));
            $results[] = $result;
        }

        return $results;
    }

    private function executeTool(string $name, array $arguments, TelegramUser $user): array
    {
        if (! $user->isAdmin() && in_array($name, ['create_listing', 'update_listing', 'delete_listing'], true)) {
            return ['success' => false, 'error' => 'You are not authorized to perform this action.'];
        }

        return match ($name) {
            'search_listings' => $this->listingTools->searchListings($arguments),
            'get_listing_by_id' => $this->listingTools->getListingById((int) ($arguments['id'] ?? 0)),
            'create_listing' => $this->listingTools->createListing($arguments),
            'update_listing' => $this->listingTools->updateListing($arguments),
            'delete_listing' => $this->listingTools->deleteListing((int) ($arguments['id'] ?? 0)),
            default => ['success' => false, 'error' => 'Unknown tool: '.$name],
        };
    }

    private function serializeToolCalls(array $toolCalls): array
    {
        $serialized = [];

        foreach ($toolCalls as $toolCall) {
            $serialized[] = [
                'id' => $toolCall['id'] ?? '',
                'type' => $toolCall['type'] ?? 'function',
                'function' => [
                    'name' => $toolCall['function']['name'] ?? '',
                    'arguments' => $toolCall['function']['arguments'] ?? '{}',
                ],
            ];
        }

        return $serialized;
    }
}
