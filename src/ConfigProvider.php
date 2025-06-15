<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory;

use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsHandler;
use CMDISP\MonologMicrosoftTeams\TeamsLogHandler;
use Elastic\Elasticsearch\Client as V8Client;
use Elasticsearch\Client as V7Client;
use JK\Monolog\Processor\RequestHeaderProcessor;
use Mimmi20\Monolog\Handler\CallbackFilterHandler;
use Mimmi20\MonologFactory\Client\ElasticsearchV7Factory;
use Mimmi20\MonologFactory\Client\ElasticsearchV8Factory;
use Mimmi20\MonologFactory\Formatter\ChromePHPFormatterFactory;
use Mimmi20\MonologFactory\Formatter\ElasticaFormatterFactory;
use Mimmi20\MonologFactory\Formatter\ElasticsearchFormatterFactory;
use Mimmi20\MonologFactory\Formatter\FlowdockFormatterFactory;
use Mimmi20\MonologFactory\Formatter\FluentdFormatterFactory;
use Mimmi20\MonologFactory\Formatter\GelfMessageFormatterFactory;
use Mimmi20\MonologFactory\Formatter\GoogleCloudLoggingFormatterFactory;
use Mimmi20\MonologFactory\Formatter\HtmlFormatterFactory;
use Mimmi20\MonologFactory\Formatter\JsonFormatterFactory;
use Mimmi20\MonologFactory\Formatter\LineFormatterFactory;
use Mimmi20\MonologFactory\Formatter\LogglyFormatterFactory;
use Mimmi20\MonologFactory\Formatter\LogmaticFormatterFactory;
use Mimmi20\MonologFactory\Formatter\LogstashFormatterFactory;
use Mimmi20\MonologFactory\Formatter\MongoDBFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Mimmi20\MonologFactory\Formatter\ScalarFormatterFactory;
use Mimmi20\MonologFactory\Formatter\SyslogFormatterFactory;
use Mimmi20\MonologFactory\Formatter\WildfireFormatterFactory;
use Mimmi20\MonologFactory\Handler\AmqpHandlerFactory;
use Mimmi20\MonologFactory\Handler\BrowserConsoleHandlerFactory;
use Mimmi20\MonologFactory\Handler\BufferHandlerFactory;
use Mimmi20\MonologFactory\Handler\CallbackFilterHandlerFactory;
use Mimmi20\MonologFactory\Handler\ChromePHPHandlerFactory;
use Mimmi20\MonologFactory\Handler\CouchDBHandlerFactory;
use Mimmi20\MonologFactory\Handler\DeduplicationHandlerFactory;
use Mimmi20\MonologFactory\Handler\DoctrineCouchDBHandlerFactory;
use Mimmi20\MonologFactory\Handler\DynamoDbHandlerFactory;
use Mimmi20\MonologFactory\Handler\ElasticaHandlerFactory;
use Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory;
use Mimmi20\MonologFactory\Handler\ErrorLogHandlerFactory;
use Mimmi20\MonologFactory\Handler\FallbackGroupHandlerFactory;
use Mimmi20\MonologFactory\Handler\FilterHandlerFactory;
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManagerFactory;
use Mimmi20\MonologFactory\Handler\FingersCrossedHandlerFactory;
use Mimmi20\MonologFactory\Handler\FirePHPHandlerFactory;
use Mimmi20\MonologFactory\Handler\FleepHookHandlerFactory;
use Mimmi20\MonologFactory\Handler\FlowdockHandlerFactory;
use Mimmi20\MonologFactory\Handler\GelfHandlerFactory;
use Mimmi20\MonologFactory\Handler\GroupHandlerFactory;
use Mimmi20\MonologFactory\Handler\IFTTTHandlerFactory;
use Mimmi20\MonologFactory\Handler\InsightOpsHandlerFactory;
use Mimmi20\MonologFactory\Handler\LogEntriesHandlerFactory;
use Mimmi20\MonologFactory\Handler\LogglyHandlerFactory;
use Mimmi20\MonologFactory\Handler\LogmaticHandlerFactory;
use Mimmi20\MonologFactory\Handler\MandrillHandlerFactory;
use Mimmi20\MonologFactory\Handler\MicrosoftTeamsHandlerFactory;
use Mimmi20\MonologFactory\Handler\MongoDBHandlerFactory;
use Mimmi20\MonologFactory\Handler\NativeMailerHandlerFactory;
use Mimmi20\MonologFactory\Handler\NewRelicHandlerFactory;
use Mimmi20\MonologFactory\Handler\NoopHandlerFactory;
use Mimmi20\MonologFactory\Handler\NullHandlerFactory;
use Mimmi20\MonologFactory\Handler\OverflowHandlerFactory;
use Mimmi20\MonologFactory\Handler\ProcessHandlerFactory;
use Mimmi20\MonologFactory\Handler\PsrHandlerFactory;
use Mimmi20\MonologFactory\Handler\PushoverHandlerFactory;
use Mimmi20\MonologFactory\Handler\RedisHandlerFactory;
use Mimmi20\MonologFactory\Handler\RedisPubSubHandlerFactory;
use Mimmi20\MonologFactory\Handler\RollbarHandlerFactory;
use Mimmi20\MonologFactory\Handler\RotatingFileHandlerFactory;
use Mimmi20\MonologFactory\Handler\SamplingHandlerFactory;
use Mimmi20\MonologFactory\Handler\SendGridHandlerFactory;
use Mimmi20\MonologFactory\Handler\SlackHandlerFactory;
use Mimmi20\MonologFactory\Handler\SlackWebhookHandlerFactory;
use Mimmi20\MonologFactory\Handler\SocketHandlerFactory;
use Mimmi20\MonologFactory\Handler\SqsHandlerFactory;
use Mimmi20\MonologFactory\Handler\StreamHandlerFactory;
use Mimmi20\MonologFactory\Handler\SymfonyMailerHandlerFactory;
use Mimmi20\MonologFactory\Handler\SyslogHandlerFactory;
use Mimmi20\MonologFactory\Handler\SyslogUdpHandlerFactory;
use Mimmi20\MonologFactory\Handler\TeamsLogHandlerFactory;
use Mimmi20\MonologFactory\Handler\TelegramBotHandlerFactory;
use Mimmi20\MonologFactory\Handler\TestHandlerFactory;
use Mimmi20\MonologFactory\Handler\WhatFailureGroupHandlerFactory;
use Mimmi20\MonologFactory\Handler\ZendMonitorHandlerFactory;
use Mimmi20\MonologFactory\Processor\ClosureContextProcessorFactory;
use Mimmi20\MonologFactory\Processor\GitProcessorFactory;
use Mimmi20\MonologFactory\Processor\HostnameProcessorFactory;
use Mimmi20\MonologFactory\Processor\IntrospectionProcessorFactory;
use Mimmi20\MonologFactory\Processor\LoadAverageProcessorFactory;
use Mimmi20\MonologFactory\Processor\MemoryPeakUsageProcessorFactory;
use Mimmi20\MonologFactory\Processor\MemoryUsageProcessorFactory;
use Mimmi20\MonologFactory\Processor\MercurialProcessorFactory;
use Mimmi20\MonologFactory\Processor\ProcessIdProcessorFactory;
use Mimmi20\MonologFactory\Processor\PsrLogMessageProcessorFactory;
use Mimmi20\MonologFactory\Processor\RequestHeaderProcessorFactory;
use Mimmi20\MonologFactory\Processor\TagProcessorFactory;
use Mimmi20\MonologFactory\Processor\UidProcessorFactory;
use Mimmi20\MonologFactory\Processor\WebProcessorFactory;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Formatter\ElasticaFormatter;
use Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Formatter\FlowdockFormatter;
use Monolog\Formatter\FluentdFormatter;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Formatter\MongoDBFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Formatter\SyslogFormatter;
use Monolog\Formatter\WildfireFormatter;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CouchDBHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\DoctrineCouchDBHandler;
use Monolog\Handler\DynamoDbHandler;
use Monolog\Handler\ElasticaHandler;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\FleepHookHandler;
use Monolog\Handler\FlowdockHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\IFTTTHandler;
use Monolog\Handler\InsightOpsHandler;
use Monolog\Handler\LogEntriesHandler;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\LogmaticHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\NoopHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\OverflowHandler;
use Monolog\Handler\ProcessHandler;
use Monolog\Handler\PsrHandler;
use Monolog\Handler\PushoverHandler;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\RedisPubSubHandler;
use Monolog\Handler\RollbarHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SamplingHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\SqsHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Handler\TestHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Logger;
use Monolog\Processor\ClosureContextProcessor;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\LoadAverageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MercurialProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\TagProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{dependencies: array{abstract_factories: array<int, class-string>, factories: array<class-string, class-string>}, monolog_handlers: array{aliases: array<string|class-string, class-string>, factories: array<string|class-string, class-string>}, monolog_processors: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog_formatters: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog_service_clients:array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'monolog' => $this->getMonologConfig(),
            'monolog_formatters' => $this->getMonologFormatterConfig(),
            'monolog_handlers' => $this->getMonologHandlerConfig(),
            'monolog_processors' => $this->getMonologProcessorConfig(),
            'monolog_service_clients' => $this->getMonologClientConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{abstract_factories: array<int, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getDependencyConfig(): array
    {
        return [
            'abstract_factories' => [
                LoggerAbstractFactory::class,
            ],
            'factories' => [
                ActivationStrategyPluginManager::class => ActivationStrategyPluginManagerFactory::class,
                MonologFormatterPluginManager::class => MonologFormatterPluginManagerFactory::class,
                MonologHandlerPluginManager::class => MonologHandlerPluginManagerFactory::class,
                MonologPluginManager::class => MonologPluginManagerFactory::class,
                MonologProcessorPluginManager::class => MonologProcessorPluginManagerFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<string|class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologHandlerConfig(): array
    {
        return [
            'aliases' => [
                'amqp' => AmqpHandler::class,
                'browserconsole' => BrowserConsoleHandler::class,
                'buffer' => BufferHandler::class,
                'callbackfilter' => CallbackFilterHandler::class,
                'chromephp' => ChromePHPHandler::class,
                'couchDb' => CouchDBHandler::class,
                'deduplication' => DeduplicationHandler::class,
                'doctrineCouchDb' => DoctrineCouchDBHandler::class,
                'dynamoDb' => DynamoDbHandler::class,
                'elastica' => ElasticaHandler::class,
                'elasticsearch' => ElasticsearchHandler::class,
                'errorlog' => ErrorLogHandler::class,
                'fallbackgroup' => FallbackGroupHandler::class,
                'filter' => FilterHandler::class,
                'fingersCrossed' => FingersCrossedHandler::class,
                'firephp' => FirePHPHandler::class,
                'fleepHook' => FleepHookHandler::class,
                'flowdock' => FlowdockHandler::class,
                'gelf' => GelfHandler::class,
                'group' => GroupHandler::class,
                'ifttt' => IFTTTHandler::class,
                'insightops' => InsightOpsHandler::class,
                'logEntries' => LogEntriesHandler::class,
                'loggly' => LogglyHandler::class,
                'logmatic' => LogmaticHandler::class,
                'mandrill' => MandrillHandler::class,
                'microsoft-teams' => MicrosoftTeamsHandler::class,
                'mongo' => MongoDBHandler::class,
                'nativemailer' => NativeMailerHandler::class,
                'newRelic' => NewRelicHandler::class,
                'noop' => NoopHandler::class,
                'null' => NullHandler::class,
                'overflow' => OverflowHandler::class,
                'process' => ProcessHandler::class,
                'psr' => PsrHandler::class,
                'pushover' => PushoverHandler::class,
                'redis' => RedisHandler::class,
                'redisPubSub' => RedisPubSubHandler::class,
                'rollbar' => RollbarHandler::class,
                'rotating' => RotatingFileHandler::class,
                'sampling' => SamplingHandler::class,
                'sendgrid' => SendGridHandler::class,
                'slack' => SlackHandler::class,
                'slackWebhook' => SlackWebhookHandler::class,
                'socket' => SocketHandler::class,
                'sqs' => SqsHandler::class,
                'stream' => StreamHandler::class,
                'symfonyMailer' => SymfonyMailerHandler::class,
                'syslog' => SyslogHandler::class,
                'syslogudp' => SyslogUdpHandler::class,
                'teams' => TeamsLogHandler::class,
                'telegrambot' => TelegramBotHandler::class,
                'test' => TestHandler::class,
                'whatFailureGroup' => WhatFailureGroupHandler::class,
                'zend' => ZendMonitorHandler::class,
            ],
            'factories' => [
                AmqpHandler::class => AmqpHandlerFactory::class,
                BrowserConsoleHandler::class => BrowserConsoleHandlerFactory::class,
                BufferHandler::class => BufferHandlerFactory::class,
                CallbackFilterHandler::class => CallbackFilterHandlerFactory::class,
                ChromePHPHandler::class => ChromePHPHandlerFactory::class,
                CouchDBHandler::class => CouchDBHandlerFactory::class,
                DeduplicationHandler::class => DeduplicationHandlerFactory::class,
                DoctrineCouchDBHandler::class => DoctrineCouchDBHandlerFactory::class,
                DynamoDbHandler::class => DynamoDbHandlerFactory::class,
                ElasticaHandler::class => ElasticaHandlerFactory::class,
                ElasticsearchHandler::class => ElasticsearchHandlerFactory::class,
                ErrorLogHandler::class => ErrorLogHandlerFactory::class,
                FallbackGroupHandler::class => FallbackGroupHandlerFactory::class,
                FilterHandler::class => FilterHandlerFactory::class,
                FingersCrossedHandler::class => FingersCrossedHandlerFactory::class,
                FirePHPHandler::class => FirePHPHandlerFactory::class,
                FleepHookHandler::class => FleepHookHandlerFactory::class,
                FlowdockHandler::class => FlowdockHandlerFactory::class,
                GelfHandler::class => GelfHandlerFactory::class,
                GroupHandler::class => GroupHandlerFactory::class,
                IFTTTHandler::class => IFTTTHandlerFactory::class,
                InsightOpsHandler::class => InsightOpsHandlerFactory::class,
                LogEntriesHandler::class => LogEntriesHandlerFactory::class,
                LogglyHandler::class => LogglyHandlerFactory::class,
                LogmaticHandler::class => LogmaticHandlerFactory::class,
                MandrillHandler::class => MandrillHandlerFactory::class,
                MicrosoftTeamsHandler::class => MicrosoftTeamsHandlerFactory::class,
                MongoDBHandler::class => MongoDBHandlerFactory::class,
                NativeMailerHandler::class => NativeMailerHandlerFactory::class,
                NewRelicHandler::class => NewRelicHandlerFactory::class,
                NoopHandler::class => NoopHandlerFactory::class,
                NullHandler::class => NullHandlerFactory::class,
                OverflowHandler::class => OverflowHandlerFactory::class,
                ProcessHandler::class => ProcessHandlerFactory::class,
                PsrHandler::class => PsrHandlerFactory::class,
                PushoverHandler::class => PushoverHandlerFactory::class,
                RedisHandler::class => RedisHandlerFactory::class,
                RedisPubSubHandler::class => RedisPubSubHandlerFactory::class,
                RollbarHandler::class => RollbarHandlerFactory::class,
                RotatingFileHandler::class => RotatingFileHandlerFactory::class,
                SamplingHandler::class => SamplingHandlerFactory::class,
                SendGridHandler::class => SendGridHandlerFactory::class,
                SlackHandler::class => SlackHandlerFactory::class,
                SlackWebhookHandler::class => SlackWebhookHandlerFactory::class,
                SocketHandler::class => SocketHandlerFactory::class,
                SqsHandler::class => SqsHandlerFactory::class,
                StreamHandler::class => StreamHandlerFactory::class,
                SymfonyMailerHandler::class => SymfonyMailerHandlerFactory::class,
                SyslogHandler::class => SyslogHandlerFactory::class,
                SyslogUdpHandler::class => SyslogUdpHandlerFactory::class,
                TeamsLogHandler::class => TeamsLogHandlerFactory::class,
                TelegramBotHandler::class => TelegramBotHandlerFactory::class,
                TestHandler::class => TestHandlerFactory::class,
                WhatFailureGroupHandler::class => WhatFailureGroupHandlerFactory::class,
                ZendMonitorHandler::class => ZendMonitorHandlerFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologProcessorConfig(): array
    {
        return [
            'aliases' => [
                'closure' => ClosureContextProcessor::class,
                'git' => GitProcessor::class,
                'hostname' => HostnameProcessor::class,
                'introspection' => IntrospectionProcessor::class,
                'load-average' => LoadAverageProcessor::class,
                'memoryPeak' => MemoryPeakUsageProcessor::class,
                'memoryUsage' => MemoryUsageProcessor::class,
                'mercurial' => MercurialProcessor::class,
                'processId' => ProcessIdProcessor::class,
                'psrLogMessage' => PsrLogMessageProcessor::class,
                'requestheader' => RequestHeaderProcessor::class,
                'tags' => TagProcessor::class,
                'uid' => UidProcessor::class,
                'web' => WebProcessor::class,
            ],
            'factories' => [
                ClosureContextProcessor::class => ClosureContextProcessorFactory::class,
                GitProcessor::class => GitProcessorFactory::class,
                HostnameProcessor::class => HostnameProcessorFactory::class,
                IntrospectionProcessor::class => IntrospectionProcessorFactory::class,
                LoadAverageProcessor::class => LoadAverageProcessorFactory::class,
                MemoryPeakUsageProcessor::class => MemoryPeakUsageProcessorFactory::class,
                MemoryUsageProcessor::class => MemoryUsageProcessorFactory::class,
                MercurialProcessor::class => MercurialProcessorFactory::class,
                ProcessIdProcessor::class => ProcessIdProcessorFactory::class,
                PsrLogMessageProcessor::class => PsrLogMessageProcessorFactory::class,
                RequestHeaderProcessor::class => RequestHeaderProcessorFactory::class,
                TagProcessor::class => TagProcessorFactory::class,
                UidProcessor::class => UidProcessorFactory::class,
                WebProcessor::class => WebProcessorFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologFormatterConfig(): array
    {
        return [
            'aliases' => [
                'chromePHP' => ChromePHPFormatter::class,
                'elastica' => ElasticaFormatter::class,
                'elasticsearch' => ElasticsearchFormatter::class,
                'flowdock' => FlowdockFormatter::class,
                'fluentd' => FluentdFormatter::class,
                'gelf' => GelfMessageFormatter::class,
                'google-cloud' => GoogleCloudLoggingFormatter::class,
                'html' => HtmlFormatter::class,
                'json' => JsonFormatter::class,
                'line' => LineFormatter::class,
                'loggly' => LogglyFormatter::class,
                'logmatic' => LogmaticFormatter::class,
                'logstash' => LogstashFormatter::class,
                'mongodb' => MongoDBFormatter::class,
                'normalizer' => NormalizerFormatter::class,
                'scalar' => ScalarFormatter::class,
                'syslog' => SyslogFormatter::class,
                'wildfire' => WildfireFormatter::class,
            ],
            'factories' => [
                ChromePHPFormatter::class => ChromePHPFormatterFactory::class,
                ElasticaFormatter::class => ElasticaFormatterFactory::class,
                ElasticsearchFormatter::class => ElasticsearchFormatterFactory::class,
                FlowdockFormatter::class => FlowdockFormatterFactory::class,
                FluentdFormatter::class => FluentdFormatterFactory::class,
                GelfMessageFormatter::class => GelfMessageFormatterFactory::class,
                GoogleCloudLoggingFormatter::class => GoogleCloudLoggingFormatterFactory::class,
                HtmlFormatter::class => HtmlFormatterFactory::class,
                JsonFormatter::class => JsonFormatterFactory::class,
                LineFormatter::class => LineFormatterFactory::class,
                LogglyFormatter::class => LogglyFormatterFactory::class,
                LogmaticFormatter::class => LogmaticFormatterFactory::class,
                LogstashFormatter::class => LogstashFormatterFactory::class,
                MongoDBFormatter::class => MongoDBFormatterFactory::class,
                NormalizerFormatter::class => NormalizerFormatterFactory::class,
                ScalarFormatter::class => ScalarFormatterFactory::class,
                SyslogFormatter::class => SyslogFormatterFactory::class,
                WildfireFormatter::class => WildfireFormatterFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologClientConfig(): array
    {
        return [
            'aliases' => [
                'v7' => V7Client::class,
                'v8' => V8Client::class,
            ],
            'factories' => [
                V7Client::class => ElasticsearchV7Factory::class,
                V8Client::class => ElasticsearchV8Factory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getMonologConfig(): array
    {
        return [
            'aliases' => [
                LoggerInterface::class => Logger::class,
            ],
            'factories' => [
                Logger::class => MonologFactory::class,
            ],
        ];
    }
}
