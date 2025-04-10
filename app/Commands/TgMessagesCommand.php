<?php

namespace App\Commands;

use App\Application;
use App\Cache\Redis;
use App\Telegram\TelegramApiImpl;
use Predis\Client;
use Psr\SimpleCache\InvalidArgumentException;

class TgMessagesCommand extends Command
{
    public function __construct(
        protected Application $app,
        private int $offset = 0,
        private array|null $oldMessages = [],
        private Redis $redis = new Redis(
            new Client([
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ])
        )
    ){}

    protected function createTelegramApi(): TelegramApiImpl
    {
        return new TelegramApiImpl($this->app->env('TELEGRAM_TOKEN'));
    }

    public function run(array $options = []): void
    {
        echo json_encode($this->receiveNewMessages());
    }

    private function receiveNewMessages(): array
    {
        try {
            $this->offset = $this->redis->get('tg_messages:offset', 0);
            $result = $this->createTelegramApi()->getMessages($this->offset);
            $this->redis->set('tg_messages:offset', $result['offset'] ?? 0);
            $this->oldMessages = json_decode($this->redis->get('tg_messages:old_messages'));
            $messages = [];
            
            foreach ($result['result'] ?? [] as $chatId => $newMessage) {
                isset($this->oldMessages[$chatId]) ? $this->oldMessages[$chatId] = [...$this->oldMessages[$chatId], ...$newMessage] :    $this->oldMessages[$chatId] = $newMessage;

                $messages[$chatId] = $this->oldMessages[$chatId];
            }
            $this->redis->set('tg_messages:old_messages', json_encode($this->oldMessages));
            return $messages;
        } catch (InvalidArgumentException) {
            return [];
        }
    }
}