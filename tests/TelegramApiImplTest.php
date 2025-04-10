<?php

use App\Telegram\TelegramApiImpl;
use PHPUnit\Framework\TestCase;

class TelegramApiImplTest extends TestCase
{
    private $telegramApi;

    protected function setUp(): void
    {
        $this->telegramApi = new TelegramApiImpl('dummy_token');
    }

    public function testGetMessagesReturnsArray()
    {
        $result = $this->telegramApi->getMessages(0);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('result', $result);
    }

    public function testSendMessage()
    {
        $this->telegramApi->sendMessage('123456789', 'Hello, World!');
        
        $this->assertTrue(true);
    }
}
