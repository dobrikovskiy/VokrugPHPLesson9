<?php

use App\Commands\TgMessagesCommand;
use App\Telegram\TelegramApiImpl;
use App\Application;
use PHPUnit\Framework\TestCase;

class TgMessagesCommandTest extends TestCase
{
    private $app;
    private $mockTelegramApi;

    protected function setUp(): void
    {
        $this->app = $this->createMock(Application::class);
        
        // Создаем мок для TelegramApiImpl
        $this->mockTelegramApi = $this->createMock(TelegramApiImpl::class);
    }

    public function testRunCallsGetMessagesAndOutputsJson()
    {
        $this->app->method('env')->willReturn('dummy_token');

        // Устанавливаем мок для TelegramApiImpl
        $tgCommand = new TgMessagesCommand($this->app);

        $expectedMessages = ['result' => ['some chat id' => ['message text']]];
        $this->mockTelegramApi->method('getMessages')->willReturn($expectedMessages);
        
        // Подменяем реализацию TelegramApiImpl на заглушку
        $this->app->method('env')->willReturn('dummy_token');

        $tgCommand = new class($this->app, $this->mockTelegramApi) extends TgMessagesCommand {
            private $mockTelegramApi;

            public function __construct(Application $app, TelegramApiImpl $mockTelegramApi)
            {
                parent::__construct($app);
                $this->mockTelegramApi = $mockTelegramApi;
            }

            protected function createTelegramApi(): TelegramApiImpl
            {
                return $this->mockTelegramApi;
            }
        };

        // Вывод результата в тесте
        ob_start();
        $tgCommand->run();
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(json_encode($expectedMessages), $output);
    }
}
