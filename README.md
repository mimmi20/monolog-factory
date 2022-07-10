# monolog-factory

[Monolog](https://github.com/Seldaek/monolog) Factories for Laminas and Mezzio

This library was inspired by [psr11-monolog](https://gitlab.com/blazon/psr11-monolog)
and [monolog-factory](https://github.com/nikolaposa/monolog-factory).

[![Latest Stable Version](https://poser.pugx.org/mimmi20/monolog-factory/v/stable?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-factory)
[![Latest Unstable Version](https://poser.pugx.org/mimmi20/monolog-factory/v/unstable?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-factory)
[![License](https://poser.pugx.org/mimmi20/monolog-factory/license?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-factory)

## Code Status

[![codecov](https://codecov.io/gh/mimmi20/monolog-factory/branch/master/graph/badge.svg)](https://codecov.io/gh/mimmi20/monolog-factory)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/mimmi20/monolog-factory.svg)](http://isitmaintained.com/project/mimmi20/monolog-factory "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/mimmi20/monolog-factory.svg)](http://isitmaintained.com/project/mimmi20/monolog-factory "Percentage of issues still open")

## Table of Contents

- [Installation](#installation)
- [Usage with Laminas and Mezzio](#usage-with-laminas-and-mezzio)
- [Configuration](#configuration)
  - [Minimal Configuration](#minimal-configuration)
  - [Full Configuration](#full-configuration)
  - [Handlers](#handlers)
    - [Log to files and syslog](#log-to-files-and-syslog)
      - [StreamHandler](#streamhandler)
      - [RotatingFileHandler](#rotatingfilehandler)
      - [SyslogHandler](#sysloghandler)
      - [ErrorLogHandler](#errorloghandler)
      - [ProcessHandler](#processhandler)
    - [Send alerts and emails](#send-alerts-and-emails)
      - [NativeMailerHandler](#nativemailerhandler)
      - [SwiftMailerHandler](#swiftmailerhandler)
      - [SymfonyMailerHandler](#symfonymailerhandler)
      - [PushoverHandler](#pushoverhandler)
      - [FlowdockHandler](#flowdockhandler)
      - [SlackWebhookHandler](#slackwebhookhandler)
      - [SlackHandler](#slackhandler)
      - [SendGridHandler](#sendgridhandler)
      - [MandrillHandler](#mandrillhandler)
      - [FleepHookHandler](#fleephookhandler)
      - [IFTTTHandler](#ifttthandler)
      - [TelegramBotHandler](#telegrambothandler)
    - [Log specific servers and networked logging](#log-specific-servers-and-networked-logging)
      - [SocketHandler](#sockethandler)
      - [AmqpHandler](#amqphandler)
      - [GelfHandler](#gelfhandler)
      - [CubeHandler](#cubehandler)
      - [ZendMonitorHandler](#zendmonitorhandler)
      - [NewRelicHandler](#newrelichandler)
      - [LogglyHandler](#logglyhandler)
      - [RollbarHandler](#rollbarhandler)
      - [SyslogUdpHandler](#syslogudphandler)
      - [LogEntriesHandler](#logentrieshandler)
      - [InsightOpsHandler](#insightopshandler)
      - [LogmaticHandler](#logmatichandler)
      - [SqsHandler](#sqshandler)
    - [Logging in development](#logging-in-development)
      - [FirePHPHandler](#firephphandler)
      - [ChromePHPHandler](#chromephphandler)
      - [BrowserConsoleHandler](#browserconsolehandler)
      - [PHPConsoleHandler](#phpconsolehandler)
    - [Log to databases](#log-to-databases)
      - [RedisHandler](#redishandler)
      - [RedisPubSubHandler](#redispubsubhandler)
      - [MongoDBHandler](#mongodbhandler)
      - [CouchDBHandler](#couchdbhandler)
      - [DoctrineCouchDBHandler](#doctrinecouchdbhandler)
      - [ElasticaHandler](#elasticahandler)
      - [ElasticsearchHandler](#elasticsearchhandler)
      - [DynamoDbHandler](#dynamodbhandler)
    - [Wrappers / Special Handlers](#wrappers--special-handlers)
      - [FingersCrossedHandler](#fingerscrossedhandler)
      - [DeduplicationHandler](#deduplicationhandler)
      - [WhatFailureGroupHandler](#whatfailuregrouphandler)
      - [FallbackGroupHandler](#fallbackgrouphandler)
      - [BufferHandler](#bufferhandler)
      - [GroupHandler](#grouphandler)
      - [FilterHandler](#filterhandler)
      - [SamplingHandler](#samplinghandler)
      - [NoopHandler](#noophandler)
      - [NullHandler](#nullhandler)
      - [PsrHandler](#psrhandler)
      - [TestHandler](#testhandler)
      - [OverflowHandler](#overflowhandler)
    - [3rd Party Handlers](#3rd-party-handlers)
      - [MicrosoftTeamsHandler](#microsoftteamshandler)
      - [TeamsLogHandler](#teamsloghandler)
      - [CallbackFilterHandler](#callbackfilterhandler)
  - [Formatters](#formatters)
    - [LineFomatter](#linefomatter)
    - [HtmlFormatter](#htmlformatter)
    - [NormalizerFormatter](#normalizerformatter)
    - [ScalarFormatter](#scalarformatter)
    - [JsonFormatter](#jsonformatter)
    - [WildfireFormatter](#wildfireformatter)
    - [ChromePHPFormatter](#chromephpformatter)
    - [GelfMessageFormatter](#gelfmessageformatter)
    - [LogstashFormatter](#logstashformatter)
    - [ElasticaFormatter](#elasticaformatter)
    - [ElasticsearchFormatter](#elasticsearchformatter)
    - [LogglyFormatter](#logglyformatter)
    - [FlowdockFormatter](#flowdockformatter)
    - [MongoDBFormatter](#mongodbformatter)
    - [LogmaticFormatter](#logmaticformatter)
    - [FluentdFormatter](#fluentdformatter)
    - [StreamFormatter](#streamformatter)
  - [Processors](#processors)
    - [PsrLogMessageProcessor](#psrlogmessageprocessor)
    - [IntrospectionProcessor](#introspectionprocessor)
    - [WebProcessor](#webprocessor)
    - [MemoryUsageProcessor](#memoryusageprocessor)
    - [MemoryPeakUsageProcessor](#memorypeakusageprocessor)
    - [ProcessIdProcessor](#processidprocessor)
    - [UidProcessor](#uidprocessor)
    - [GitProcessor](#gitprocessor)
    - [MercurialProcessor](#mercurialprocessor)
    - [TagProcessor](#tagprocessor)
    - [HostnameProcessor](#hostnameprocessor)

## Installation

Run

```shell
composer require mimmi20/monolog-factory
```

## Usage with Laminas and Mezzio

You'll need to add configuration and register the services you'd like to use. There are number of ways to do that
but the recommended way is to create a new config file `config/autoload/logger.config.php`

## Configuration

config/autoload/logger.config.php

```php
<?php
return [
    'monolog' => [
        'default' => [
            'name' => 'name',
            'errorLevelMap' => false, // optional: map of php errors to monolog levels, use an empty array if the default map should be used, defaults to false which disables the error handling
            'exceptionLevelMap' => false, // optional: map of exceptions to monolog levels, use an empty array if the default map should be used, defaults to false which disables the exception handling
            'fatalLevel' => false, // optional: monolog level used to log fatal errors, use null if the default level should be used, defaults to false which disables the handling of fatal errors
            'handlers' => [ // Handlers for Monolog
                // At the bare minimum you must include a default handler config.
                // Otherwise log entries will be sent to the void.
                'default' => [
                    'type' => 'stream',
                    'enabled' => true,
                    'options' => [
                        'stream' => '/var/log/some-log-file.txt',
                    ],
                ],
                
                // Another Handler
                'myOtherHandler' => [
                    'type' => 'stream',
                    'enabled' => false,
                    'options' => [
                        'stream' => '/var/log/someother-log-file.txt',
                    ],
                ],
            ],
            'processors' => [],
        ],
    ],
];
```

## Minimal Configuration

A minimal configuration would consist of at least one default handler and one named service.
Please note that if you don't specify a default handler a [NullHandler](#nullhandler) will be used
when you wire up the default logger.

### Minimal Example (using Mezzio for the example)

```php
<?php

return [
    'monolog' => [
        'default' => [
            'name' => 'name',
            'handlers' => [
                'default' => [
                    'type' => 'stream',
                    'options' => [
                        'stream' => '/var/log/some-log-file.txt',
                    ],
                ],
            ],
        ],
    ],
];
```

## Full Configuration

### Full Example

```php
<?php

return [
    'monolog' => [
        'default' => [
            'name' => 'name',
            'handlers' => [
                'default' => [
                    // A Handler type or pre-configured service from the container
                    'type' => 'stream',
                    
                    // Handler specific options.  See handlers below
                    'options' => [
                        'stream' => '/tmp/log_one.txt',
                    
                        // Optional: Formatter for the handler.
                        'formatter' => [
                            'type' => 'line',
                                
                            // formatter specific options.  See formatters below
                            'options' => [], 
                        ], 
                        
                        // Optional: Processor for the handler
                        'processors' => [
                            [
                                // A processor type or pre-configured service from the container
                                'type' => 'psrLogMessage',
                                
                                // processor specific options.  See processors below
                                'options' => [], 
                            ],
                        ],
                    ], 
                ],
            ],
            
            'processors' => [
                // Array Keys are the names used for the processors
                'processorOne' => [
                    // A processor type or pre-configured service from the container
                    'type' => 'psrLogMessage',
                    
                    // processor specific options.  See processors below
                    'options' => [], 
                ],        
            ],
        ],
    ],
];
```

## Handlers

### Log to files and syslog

#### StreamHandler

Logs records into any PHP stream, use this for log files.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'stream',
                    
                    'options' => [
                        'stream' => '/tmp/stream_test.txt', // Required:  File Path | Resource | Service Name
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'filePermission' => null, // Optional: file permissions (default (0644) are only for owner read/write)
                        'useLocking' => false, // Optional: Try to lock log file before doing any writes
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [StreamHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/StreamHandler.php)

#### RotatingFileHandler

Logs records to a file and creates one logfile per day. It will also delete files older than $maxFiles.
You should use [logrotate] for high profile setups though,
this is just meant as a quick and dirty solution.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'rotating',
                    
                    'options' => [
                        'filename' => '/tmp/stream_test.txt', // Required:  File Path
                        'maxFiles' => 0, // Optional:  The maximal amount of files to keep (0 means unlimited)
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'filePermission' => null, // Optional: file permissions (default (0644) are only for owner read/write)
                        'useLocking' => false, // Optional: Try to lock log file before doing any writes
                        'filenameFormat' => '{filename}-{date}', // Optional
                        'dateFormat' => 'Y-m-d', // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [RotatingFileHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/RotatingFileHandler.php)

#### SyslogHandler

Logs records to the syslog.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'syslog',
                    
                    'options' => [
                        'ident' => '/tmp/stream_test.txt', // Required:  The string ident is added to each message. 
                        'facility' => LOG_USER, // Optional:  The facility argument is used to specify what type of program is logging the message.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'logOpts' => LOG_PID, // Optional: Option flags for the openlog() call, defaults to LOG_PID
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SyslogHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SyslogHandler.php)
PHP openlog(): [openlog](http://php.net/manual/en/function.openlog.php)

#### ErrorLogHandler

Logs records to PHP's [error_log()](http://docs.php.net/manual/en/function.error-log.php) function.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'errorlog',
                    
                    'options' => [
                        'messageType' => \Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, // Optional:  Says where the error should go.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'expandNewlines' => false, // Optional: If set to true, newlines in the message will be expanded to be take multiple log entries
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ErrorLogHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ErrorLogHandler.php)

#### ProcessHandler

Logs records to the [STDIN](https://en.wikipedia.org/wiki/Standard_streams#Standard_input_.28stdin.29) of any process,
specified by a command.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'process',
                      
                    'options' => [
                        'command' => 'some-command', // Command for the process to start. Absolute paths are recommended, especially if you do not use the $cwd parameter.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'cwd' => __DIR__, // Optional: "Current working directory" (CWD) for the process to be executed in.
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ProcessHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ProcessHandler.php)

### Send alerts and emails

#### NativeMailerHandler

Sends emails using PHP's [mail()](http://php.net/manual/en/function.mail.php) function.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'nativeMailer',
                      
                    'options' => [
                        'to' => ['email1@test.com', 'email2@test.com'], // The receiver of the mail. Can be an array or string
                        'subject' => 'Error Log', // The subject of the mail
                        'from' => 'sender@test.com', // The sender of the mail
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'maxColumnWidth' => 80, // Optional: The maximum column width that the message lines will have
                        'contentType' => 'text/html', // Optional
                        'encoding' => 'utf-8', // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [NativeMailerHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/NativeMailerHandler.php)

#### SwiftMailerHandler

Sends emails using a [Swift_Mailer](http://swiftmailer.org/) instance.

```php
<?php

return [
    'monolog' => [
        'default' => [ 
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'swiftMailer',
                      
                    'options' => [
                        'mailer' => 'my-service', // The mailer to use.  Must be a valid service name in the container
                        'message' => 'my-message', // An example message for real messages, only the body will be replaced.  Must be a valid service name or callable
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SwiftMailerHandler](https://github.com/Seldaek/monolog/blob/2.x/src/Monolog/Handler/SwiftMailerHandler.php)

#### SymfonyMailerHandler

Sends emails using a [symfony/mailer](https://symfony.com/doc/current/mailer.html) instance.

```php
<?php

return [
    'monolog' => [
        'default' => [ 
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'symfonyMailer',
                      
                    'options' => [
                        'mailer' => 'my-service', // The mailer to use.  Must be a valid service name in the container
                        'email-template' => 'my-template', // An email template, the subject/body will be replaced
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SymfonyMailerHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SymfonyMailerHandler.php)

#### PushoverHandler

Sends mobile notifications via the [Pushover](https://www.pushover.net/) API. Requires the sockets Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'pushover',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Pushover api token
                        'users' => ['email1@test.com', 'email2@test.com'], // Pushover user id or array of ids the message will be sent to
                        'title' => 'Error Log', // Optional: Title sent to the Pushover API
                        'level' => \Psr\Log\LogLevel::INFO, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => false, // Optional:  Whether the messages that are handled can bubble up the stack or not
                        'useSSL' => false, // Optional:  Whether to connect via SSL. Required when pushing messages to users that are not the pushover.net app owner. OpenSSL is required for this option.
                        'highPriorityLevel' => \Psr\Log\LogLevel::WARNING, // Optional: The minimum logging level at which this handler will start sending "high priority" requests to the Pushover API
                        'emergencyLevel' => \Psr\Log\LogLevel::ERROR, // Optional: The minimum logging level at which this handler will start sending "emergency" requests to the Pushover API
                        'retry' => 22, // Optional: The retry parameter specifies how often (in seconds) the Pushover servers will send the same notification to the user.
                        'expire' => 300, // Optional: The expire parameter specifies how many seconds your notification will continue to be retried for (every retry seconds).
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [PushoverHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/PushoverHandler.php)

#### FlowdockHandler

Logs records to a [Flowdock](https://www.flowdock.com/) account. Requires the openssl and sockets Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'flowdock',
                    
                    'options' => [
                        'apiToken' => 'sometokenhere', // HipChat API Token
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FlowdockHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/FlowdockHandler.php)

#### SlackWebhookHandler

Logs records to a [Slack](https://www.slack.com/) account using Slack Webhooks. Requires the curl Excension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'slackWebhook',
                      
                    'options' => [
                        'webhookUrl' => 'webhook.slack.com', // Slack Webhook URL
                        'channel' => 'channel', // Slack channel (encoded ID or name)
                        'userName' => 'log', // Name of a bot
                        'useAttachment' => false, // Optional: Whether the message should be added to Slack as attachment (plain text otherwise)
                        'iconEmoji' => null, // Optional: The emoji name to use (or null)
                        'useShortAttachment' => true, // Optional: Whether the the context/extra messages added to Slack as attachments are in a short style
                        'includeContextAndExtra' => true, // Optional: Whether the attachment should include context and extra data
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => false, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'excludeFields' => ['context.field1', 'extra.field2'], // Optional: Dot separated list of fields to exclude from slack message.
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SlackWebhookHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SlackWebhookHandler.php)

#### SlackHandler

Logs records to a [SlackHandler](https://www.slack.com/) account using the Slack API (complex setup). Requires the
openssl and sockets Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'slack',
                      
                    'options' => [
                        'token' => 'apiToken', // Slack API token
                        'channel' => 'channel', // Slack channel (encoded ID or name)
                        'userName' => 'log', // Name of a bot
                        'useAttachment' => false, // Optional: Whether the message should be added to Slack as attachment (plain text otherwise)
                        'iconEmoji' => null, // Optional: The emoji name to use (or null)
                        'useShortAttachment' => true, // Optional: Whether the the context/extra messages added to Slack as attachments are in a short style
                        'includeContextAndExtra' => true, // Optional: Whether the attachment should include context and extra data
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => false, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'excludeFields' => ['context.field1', 'extra.field2'], // Optional: Dot separated list of fields to exclude from slack message.
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SlackHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SlackHandler.php)

#### SendGridHandler

Sends emails via the SendGrid API. Requires the curl Excension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'sendgrid',
                      
                    'options' => [
                        'apiUser' => 'apiUser', // The SendGrid API User
                        'apiKey' => 'apiKey', // The SendGrid API Key
                        'from' => 'from', // The sender of the email
                        'to' => 'to', // string or array of recipients
                        'subject' => 'subject', // The subject of the mail
                        'level' => \Psr\Log\LogLevel::INFO, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => false, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SendGridHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SendGridHandler.php)

#### MandrillHandler

Sends emails via the [Mandrill](http://www.mandrill.com/) API using a [Swift_Message](http://swiftmailer.org/) instance.
Requires the curl Excension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'mandrill',
                      
                    'options' => [
                        'apiKey' => 'my-service', // A valid Mandrill API key
                        'message' => 'my-message', // An example \Swiftmail message for real messages, only the body will be replaced.  Must be a valid service name or callable
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MandrillHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/MandrillHandler.php)

#### FleepHookHandler

Logs records to a [Fleep](https://fleep.io/) conversation using Webhooks. Requires the openssl and sockets Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'fleepHook',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Webhook token
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FleepHookHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/FleepHookHandler.php)

#### IFTTTHandler

IFTTTHandler uses cURL to trigger IFTTT Maker actions. Requires the curl Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'ifttt',
                      
                    'options' => [
                        'eventName' => 'eventName', // name of an event
                        'secretKey' => 'secretKey',
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [IFTTTHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/IFTTTHandler.php)

#### TelegramBotHandler

Logs records to a [Telegram](https://core.telegram.org/bots/api) bot account. Requires the curl Excension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'telegrambot',
                      
                    'options' => [
                        'apiKey' => 'api-key', // Api Key
                        'channel' => 'channel', // Channel
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'parseMode' => null, // Optional: null or one of 'HTML', 'MarkdownV2', 'Markdown'
                        'disableWebPagePreview' => null, // Optional: null or boolean
                        'disableNotification' => null, // Optional: null or boolean
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [TelegramBotHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/TelegramBotHandler.php)

### Log specific servers and networked logging

#### SocketHandler

Logs records to [sockets](http://php.net/fsockopen), use this for UNIX and TCP sockets. See
an [example](https://github.com/Seldaek/monolog/blob/master/doc/sockets.md). Requires the sockets Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'socket',
                      
                    'options' => [
                        'connectionString' => 'unix:///var/log/httpd_app_log.socket', // Socket connection string.  You can use a unix:// prefix to access unix sockets and udp:// to open UDP sockets instead of the default TCP.
                        'timeout' => 30.0, // Optional: The connection timeout, in seconds.
                        'writeTimeout' => 90.0, // Optional: Set timeout period on a stream.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SocketHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SocketHandler.php)

#### AmqpHandler

Logs records to an [AMQP](http://www.amqp.org/) compatible server. Requires
the [php-amqp](http://pecl.php.net/package/amqp) extension (1.0+) or
the [php-amqplib](https://github.com/php-amqplib/php-amqplib) library.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'amqp',
                      
                    'options' => [
                        'exchange' => 'my-service', // AMQPExchange (php AMQP ext) or PHP AMQP lib channel.  Must be a valid service.
                        'exchangeName' => 'log-name', // Optional: Exchange name, for AMQPChannel (PhpAmqpLib) only
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [AmqpHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/AmqpHandler.php)

#### GelfHandler

Logs records to a [Graylog2](http://www.graylog2.org) server. Requires
package [graylog2/gelf-php](https://github.com/bzikarsky/gelf-php).

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'gelf',
                      
                    'options' => [
                        'publisher' => 'my-service', // A Gelf\PublisherInterface object.  Must be a valid service.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [GelfHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/GelfHandler.php)

#### CubeHandler

Logs records to a [Cube] server. Requires the sockets Extension for https requests or the curl Extension for http
requests.

_Note: Cube is not under active development, maintenance or support by
Square (or by its original author Mike Bostock). It has been deprecated
internally for over a year._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'cube',
                      
                    'options' => [
                        'url' => 'http://test.com:80', // A valid url.  Must consist of three parts : protocol://host:port
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [CubeHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/CubeHandler.php)

#### ZendMonitorHandler

Logs records to the Zend Monitor present in [Zend Server](http://www.zend.com/en/products/zend_server).

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'zend',
                      
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ZendMonitorHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ZendMonitorHandler.php)

#### NewRelicHandler

Logs records to a [NewRelic](http://newrelic.com/) application. Requires the newrelic Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'newRelic',
                      
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'appName' => 'my-app', // Optional: Application name
                        'explodeArrays' => false, // Optional: Explode Arrays
                        'transactionName' => 'my-transaction', // Optional: Explode Arrays
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [NewRelicHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/NewRelicHandler.php)

#### LogglyHandler

Logs records to a [Loggly](http://www.loggly.com/) account. Requires the curl Excension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'loggly',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Webhook token
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogglyHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/LogglyHandler.php)

#### RollbarHandler

Logs records to a [Rollbar](https://rollbar.com/) account.

_Note: RollerbarHandler is out of date with upstream changes. In addition the Rollerbar library suggests using
the PsrHandler instead. See [Rollerbar Docs](https://github.com/rollbar/rollbar-php#using-monolog) for how to set this
up.

Monolog Docs: [RollbarHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/RollbarHandler.php)

#### SyslogUdpHandler

Logs records to a remote [Syslogd](http://www.rsyslog.com/) server. Requires the sockets Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'syslogUdp',
                      
                    'options' => [
                        'host' => 'somewhere.com', // Host
                        'port' => 513, //  Optional: Port
                        'facility' => 'Me', // Optional: Facility
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'ident' => 'me-too', // Optional: Program name or tag for each log message.
                        'rfc' => '', // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SyslogUdpHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SyslogUdpHandler.php)

#### LogEntriesHandler

Logs records to a [LogEntries](http://logentries.com/) account. Requires the openssl and sockets Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'logEntries',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Log token supplied by LogEntries
                        'useSSL' => true, // Optional: Whether or not SSL encryption should be used.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        'host' => 'data.logentries.com', // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogEntriesHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/LogEntriesHandler.php)

#### InsightOpsHandler

Logs records to an [InsightOps](https://www.rapid7.com/products/insightops/) account. Requires the openssl and sockets
Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'insightops',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Log token supplied by InsightOps
                        'region' => 'region', // Region where InsightOps account is hosted. Could be 'us' or 'eu'.
                        'useSSL' => true, // Optional: Whether or not SSL encryption should be used.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [InsightOpsHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/InsightOpsHandler.php)

#### LogmaticHandler

Logs records to a [Logmatic](http://logmatic.io/) account. Requires the openssl and sockets Extensions.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'logmatic',
                      
                    'options' => [
                        'token' => 'sometokenhere', // Log token supplied by Logmatic.
                        'hostname' => 'region', //  Optional: Host name supplied by Logmatic.
                        'appname' => 'region', //  Optional: Application name supplied by Logmatic.
                        'useSSL' => true, // Optional: Whether or not SSL encryption should be used.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'timeout' => 10.0, // Optional
                        'writeTimeout' => 5.0, // Optional
                        'persistent' => false, // Optional
                        'chunkSize' => 100, // Optional
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogmaticHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/LogmaticHandler.php)

#### SqsHandler

Logs records to an [AWS SQS](http://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-sqs.html) queue.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'sqs',
                      
                    'options' => [
                        'sqsClient' => 'my-service', // SQS Client.  Must be a valid service name in the container.
                        'queueUrl' => 'url', // URL to SQS Queue
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SqsHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SqsHandler.php)

### Logging in Development

#### FirePHPHandler

Handler for [FirePHP](http://www.firephp.org/), providing inline console messages
within [FireBug](http://getfirebug.com/).

_Note: The Firebug extension isn't being developed or maintained any longer._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'firePHP',
                      
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FirePHPHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/LogEntriesHandler.php)

#### ChromePHPHandler

Handler for [ChromePHP](http://www.chromephp.com/), providing inline console messages within Chrome. Requires the json
Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'chromePHP',
                      
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ChromePHPHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ChromePHPHandler.php)

#### BrowserConsoleHandler

Handler to send logs to browser's Javascript console with no browser extension required. Most browsers supporting
console API are supported.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'browserConsole',
                      
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [BrowserConsoleHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/BrowserConsoleHandler.php)

#### PHPConsoleHandler

Handler for [PHP Console],
providing inline console and notification popup messages within Chrome. Requires
package [barbushin/php-console](https://github.com/barbushin/php-console#installation).

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'phpConsole',
                      
                    'options' => [
                        'options' => [], // Optional: See \Monolog\Handler\PHPConsoleHandler::$options for more details
                        'connector' => 'my-service', // Optional:  Instance of \PhpConsole\Connector class. Must be a valid service.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [PHPConsoleHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/PHPConsoleHandler.php)

### Log to databases

#### RedisHandler

Logs records to a [Redis](http://redis.io/) server. Requires the [php-redis](https://pecl.php.net/package/redis)
extension or the [Predis](https://github.com/nrk/predis) library.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'redis',
                      
                    'options' => [
                        'client' => 'my-redis-service-name', // The redis instance.  Must be either a [Predis] client OR a Pecl Redis instance
                        'key' => 'my-service', // The key name to push records to
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'capSize' => true, // Optional: Number of entries to limit list size to, 0 = unlimited
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [RedisHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/RedisHandler.php)

#### RedisPubSubHandler

Logs records to a [Redis](http://redis.io/) server. Requires the [php-redis](https://pecl.php.net/package/redis)
extension or the [Predis](https://github.com/nrk/predis) library.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'redisPubSub',
                      
                    'options' => [
                        'client' => 'my-redis-service-name', // The redis instance.  Must be either a [Predis] client OR a Pecl Redis instance
                        'key' => 'my-service', // The key name to push records to
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [RedisPubSubHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/RedisPubSubHandler.php)

#### MongoDBHandler

Handler to write records in MongoDB via a [Mongo extension](http://php.net/manual/en/mongodb.tutorial.library.php)
connection.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'mongo',
                      
                    'options' => [
                        'client' => 'my-mongo-service-name', // MongoDB library or driver instance.
                        'database' => 'my-db', // Database name
                        'collection' => 'collectionName', // Collection name
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MongoDBHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/MongoDBHandler.php)

#### CouchDBHandler

Logs records to a CouchDB server.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'couchDb',
                      
                    'options' => [
                        'host' => 'localhost', // Optional: Hostname/Ip address,  Default: 'localhost'
                        'port' => 5984, // Optional: port,  Default: 5984
                        'dbname' => 'db', // Optional: Database Name,  Default: 'logger'
                        'username' => 'someuser', // Optional: Username,  Default: null
                        'password' => 'somepass', // Optional: Password,  Default: null
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [CouchDBHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/CouchDBHandler.php)

#### DoctrineCouchDBHandler

Logs records to a CouchDB server via the Doctrine CouchDB ODM.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'doctrineCouchDb',
                      
                    'options' => [
                        'client' => 'my-service', //  CouchDBClient service name.  Must be a valid container service
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [DoctrineCouchDBHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/DoctrineCouchDBHandler.php)

#### ElasticaHandler

Logs records to an Elastic Search server. Requires [Elastica](https://github.com/ruflin/Elastica).

_Note: The version of the client should match the server version, but there is actually no 8.x version._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'elastica',
                      
                    'options' => [
                        'client' => 'my-service', //  Elastica Client object.  Must be a valid container service
                        'index' => 'log', // Optional: Elastic index name
                        'type' => 'record', // Optional: Elastic document type
                        'ignoreError' => false, // Optional: Suppress Elastica exceptions
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ElasticaHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ElasticaHandler.php)

#### ElasticsearchHandler

Logs records to an Elastic Search server. Requires
the [Elasticsearch PHP client](https://github.com/elastic/elasticsearch-php).

_Note: The version of the client should match the server version._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'elasticsearch',
                      
                    'options' => [
                        'client' => 'my-service', //  Elastica Client object.  Must be a valid container service
                        'index' => 'log', // Optional: Elastic index name
                        'dateFormat' => \Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory::INDEX_PER_DAY, // Optional: possible Values are \Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory::INDEX_PER_DAY, \Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory::INDEX_PER_MONTH and \Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory::INDEX_PER_YEAR
                        'indexNameFormat' => '{indexname}', // Optional: a string which must contain the string '{indexname}' (which is a placeholder for the `index`) and may contain the string '{date}' (which is a placeholder for the actual date formatted by `dateFormat`)
                        'type' => 'record', // Optional: Elastic document type
                        'ignoreError' => false, // Optional: Suppress Elastica exceptions
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ElasticsearchHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ElasticsearchHandler.php)

#### DynamoDbHandler

Logs records to a DynamoDB table with the [AWS SDK](https://github.com/aws/aws-sdk-php).

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'dynamoDb',
                      
                    'options' => [
                        'client' => 'my-service', //  DynamoDbClient object.  Must be a valid container service
                        'table' => 'log', // Table name
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [DynamoDbHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/DynamoDbHandler.php)

### Wrappers / Special Handlers

#### FingersCrossedHandler

A very interesting wrapper. It takes a logger as parameter and will accumulate log
records of all levels until a record exceeds the defined severity level. At which
point it delivers all records, including those of lower severity, to the handler it
wraps. This means that until an error actually happens you will not see anything in
your logs, but when it happens you will have the full information, including debug and
info records. This provides you with all the information you need, but only when you
need it.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'fingersCrossed',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'activationStrategy' => 'my-service', // Optional: Strategy which determines when this handler takes action.  Must be either the error level or configured ActivationStrategyInterface service
                        'bufferSize' => 0, // Optional: How many entries should be buffered at most, beyond that the oldest items are removed from the buffer.
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'stopBuffering' => true, // Optional: Whether the handler should stop buffering after being triggered (default true)
                        'passthruLevel' => null, // Optional: Minimum level to always flush to handler on close, even if strategy not triggered
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FingersCrossedHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/FingersCrossedHandler.php)

#### DeduplicationHandler

Useful if you are sending notifications or emails when critical errors occur. It takes
a logger as parameter and will accumulate log records of all levels until the end
of the request (or flush() is called). At that point it delivers all records to
the handler it wraps, but only if the records are unique over a given time
period (60 seconds by default). If the records are duplicates they are simply
discarded. The main use of this is in case of critical failure like if your database
is unreachable for example all your requests will fail and that can result in a lot
of notifications being sent. Adding this handler reduces the amount of notifications
to a manageable level.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'deduplication',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'deduplicationStore' => '/tmp/somestore', // Optional: The file/path where the deduplication log should be kept
                        'deduplicationLevel' => \Psr\Log\LogLevel::ERROR, // Optional:The minimum logging level for log records to be looked at for deduplication purposes
                        'time' => 60, // Optional: The period (in seconds) during which duplicate entries should be suppressed after a given log is sent through
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [DeduplicationHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/DeduplicationHandler.php)

#### WhatFailureGroupHandler

This handler extends the GroupHandler ignoring exceptions raised by each child handler.
This allows you to ignore issues where a remote tcp connection may have died but you
do not want your entire application to crash and may wish to continue to log to other handlers.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'whatFailureGroup',
                    'options' => [
                        'handlers' => [], // Required: Array of Handlers to wrap
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [WhatFailureGroupHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/WhatFailureGroupHandler.php)

#### FallbackGroupHandler

This handler extends the GroupHandler ignoring exceptions raised by
each child handler, until one has handled without throwing. This allows
you to ignore issues where a remote tcp connection may have died but you
do not want your entire application to crash and may wish to continue to
attempt log to other handlers, until one does not throw.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'fallbackgroup',
                    'options' => [
                        'handlers' => [], // Required: Array of Registered Handlers to wrap
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FallbackGroupHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/FallbackGroupHandler.php)

#### BufferHandler

This handler will buffer all the log records it receives until close() is called at which point it
will call handleBatch() on the handler it wraps with all the log messages at once. This is very
useful to send an email with all records at once for example instead of having one mail for
every log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'buffer',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'bufferLimit' => 0, // Optional: How many entries should be buffered at most, beyond that the oldest items are removed from the buffer.
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        'flushOnOverflow' => true, // Optional: If true, the buffer is flushed when the max size has been reached, by default oldest entries are discarded
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [BufferHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/BufferHandler.php)

#### GroupHandler

This handler groups other handlers. Every record received is sent to all the handlers it is configured with.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'group',
                    'options' => [
                        'handlers' => [], // Required: Array of Registered Handlers to wrap
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [GroupHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/GroupHandler.php)

#### FilterHandler

Simple handler wrapper that filters records based on a list of levels

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'filter',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'minLevelOrList' => \Psr\Log\LogLevel::DEBUG, // Optional: An array of levels to accept or a minimum level if maxLevel is provided
                        'maxLevel' => \Psr\Log\LogLevel::EMERGENCY, // Optional: Maximum level to accept, only used if $minLevelOrList is not an array
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FilterHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/FilterHandler.php)

#### SamplingHandler

A sampled event stream can be useful for logging high frequency events in
a production environment where you only need an idea of what is happening
and are not concerned with capturing every occurrence. Since the decision to
handle or not handle a particular event is determined randomly, the
resulting sampled log is not guaranteed to contain 1/N of the events that
occurred in the application, but based on the Law of large numbers, it will
tend to be close to this ratio with a large number of attempts.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'sampling',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'factor' => 5, // Required: Sample factor
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [SamplingHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/SamplingHandler.php)

#### NoopHandler

This handler handles anything by doing nothing. It does not stop
processing the rest of the stack. This can be used for testing, or to
disable a handler when overriding a configuration.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'noop',
                    'options' => [],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [NoopHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/NullHandler.php)

#### NullHandler

Any record it can handle will be thrown away. This can be used
to put on top of an existing stack to override it temporarily.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'null',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [NullHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/NullHandler.php)

#### PsrHandler

Can be used to forward log records to an existing PSR-3 logger

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'psr',
                    'options' => [
                        'logger' => 'loggerService', // Required: Logger Service to wrap from the container
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [PsrHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/PsrHandler.php)

#### TestHandler

Used for testing, it records everything that is sent to it and has accessors to read out the information.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'test',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [TestHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/TestHandler.php)

#### OverflowHandler

This handler will buffer all the log messages it receives, up until a
configured threshold of number of messages of a certain lever is
reached, after it will pass all log messages to the wrapped handler.
Useful for applying in batch processing when you're only interested in
significant failures instead of minor, single erroneous events.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'overflow',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        'thresholdMap' => [ // Optional: threshold map
                            'debug' => 0, // Optional: debug threshold.  Default: 0
                            'info' => 0, // Optional: info threshold.  Default: 0
                            'notice' => 0, // Optional: notice threshold.  Default: 0
                            'warning' => 0, // Optional: warning threshold.  Default: 0
                            'error' => 0, // Optional: error threshold.  Default: 0
                            'critical' => 0, // Optional: critical threshold.  Default: 0
                            'alert' => 0, // Optional: alert threshold.  Default: 0
                            'emergency' => 0, // Optional: emergency threshold.  Default: 0
                        ],
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [OverflowHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/OverflowHandler.php)

### 3rd Party Handlers

#### MicrosoftTeamsHandler

Sends Records to a Microsoft Teams Webhook. Requires
package [actived/microsoft-teams-notifier](https://github.com/actived/microsoft-teams-notifier)

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'microsoft-teams',
                    'options' => [
                        'url' => '', // Required: Url of the MS Teams Webhook
                        'title' => '', // Optional: Default Message Title
                        'subject' => '', // Optional: Message Subject
                        'emoji' => '', // Optional: custom emoji for the Message (added to the title)
                        'color' => '', // Optional: custom color for the Message
                        'format' => '', // Optional: Message format (only used in the default formatter)
                        
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

#### TeamsLogHandler

Sends Records to a Microsoft Teams Webhook. Requires
package [cmdisp/monolog-microsoft-teams](https://github.com/cmdisp/monolog-microsoft-teams)

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'teams',
                    'options' => [
                        'url' => '', // Required: Url of the MS Teams Webhook
                        
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'formatter' => [], // Optional: Formatter for the handler.
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

#### CallbackFilterHandler

Filters Records with a Callback function.
Requires [bartlett/monolog-callbackfilterhandler](https://github.com/llaville/monolog-callbackfilterhandler)

```php
<?php

return [
    'monolog' => [
        'default' => [
            'handlers' => [
                'myHandlerName' => [
                    'type' => 'callbackfilter',
                    'options' => [
                        'handler' => [], // Required: Registered Handler to wrap
                        
                        'filters' => [], // Optional: An array of callback functions
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this handler will be triggered
                        'bubble' => true, // Optional: Whether the messages that are handled can bubble up the stack or not
                        
                        'processors' => [], // Optional: Processors for the handler.
                    ],
                ],
            ],
        ],
    ],
];
```

## Formatters

### LineFomatter

Formats a log record into a one-line string.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'line',
                    'options' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n", // Optional
                        'dateFormat' => "c", // Optional : The format of the timestamp: one supported by DateTime::format
                        'allowInlineLineBreaks' => false, // Optional : Whether to allow inline line breaks in log entries
                        'ignoreEmptyContextAndExtra' => false, // Optional
                        'includeStacktraces' => false, // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php)

### HtmlFormatter

Used to format log records into a human readable html table, mainly suitable for emails.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'html',
                    'options' => [
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [HtmlFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/HtmlFormatter.php)

### NormalizerFormatter

Normalizes objects/resources down to strings so a record can easily be serialized/encoded. Requires the json Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'normalizer',
                    'options' => [
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [NormalizerFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/NormalizerFormatter.php)

### ScalarFormatter

Used to format log records into an associative array of scalar values.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'scalar',
                    'options' => [
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ScalarFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/ScalarFormatter.php)

### JsonFormatter

Encodes a log record into json.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'json',
                    'options' => [
                        'batchMode' => \Monolog\Formatter\JsonFormatter::BATCH_MODE_JSON, // Optional
                        'appendNewline' => true, // Optional
                        'ignoreEmptyContextAndExtra' => false, // Optional
                        'includeStacktraces' => false, // Optional
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [JsonFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/JsonFormatter.php)

### WildfireFormatter

Used to format log records into the Wildfire/FirePHP protocol, only useful for the FirePHPHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'wildfire',
                    'options' => [
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [WildfireFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/WildfireFormatter.php)

### ChromePHPFormatter

Used to format log records into the ChromePHP format, only useful for the ChromePHPHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'chromePHP',
                    'options' => [], // No options available
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ChromePHPFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/ScalarFormatter.php)

### GelfMessageFormatter

Used to format log records into Gelf message instances, only useful for the GelfHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'gelf',
                    'options' => [
                        'systemName' => "my-system", // Optional : the name of the system for the Gelf log message, defaults to the hostname of the machine
                        'extraPrefix' => "extra_", // Optional : a prefix for 'extra' fields from the Monolog record
                        'contextPrefix' => 'ctxt_', // Optional : a prefix for 'context' fields from the Monolog record
                        'maxLength' => 32766, // Optional : Length per field
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [GelfMessageFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/GelfMessageFormatter.php)

### LogstashFormatter

Used to format log records into logstash event json, useful for any handler listed under inputs here.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'logstash',
                    'options' => [
                        'applicationName' => 'app-name', // the application that sends the data, used as the "type" field of logstash
                        'systemName' => "my-system", // Optional : the system/machine name, used as the "source" field of logstash, defaults to the hostname of the machine
                        'extraPrefix' => "extra_", // Optional : prefix for extra keys inside logstash "fields"
                        'contextPrefix' => 'ctxt_', // Optional : prefix for context keys inside logstash "fields", defaults to ctxt_
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogstashFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LogstashFormatter.php)

### ElasticaFormatter

Used to format log records into an Elastica Document.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'ElasticaFormatter' => [
                    'type' => 'elastica',
                    'options' => [
                        'index' => 'some-index', // Elastic search index name
                        'type' => "doc-type", // Elastic search document type
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ElasticaFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/ElasticaFormatter.php)

### ElasticsearchFormatter

Used to format log records into an Elasticsearch Document.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'ElasticsearchFormatter' => [
                    'type' => 'elasticsearch',
                    'options' => [
                        'index' => 'some-index', // Elastic search index name
                        'type' => "doc-type", // Elastic search document type
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ElasticsearchFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/ElasticsearchFormatter.php)

### LogglyFormatter

Used to format log records into Loggly messages, only useful for the LogglyHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'loggly',
                    'options' => [
                        'batchMode' => \Monolog\Formatter\JsonFormatter::BATCH_MODE_NEWLINES, // Optional
                        'appendNewline' => false, // Optional
                        'includeStacktraces' => false, // Optional
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogglyFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LogglyFormatter.php)

### FlowdockFormatter

Used to format log records into Flowdock messages, only useful for the FlowdockHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'flowdock',
                    'options' => [
                        'source' => 'Some Source',
                        'sourceEmail' => 'source@email.com'
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FlowdockFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/FlowdockFormatter.php)

### MongoDBFormatter

Converts \DateTime instances to \MongoDate and objects recursively to arrays, only useful with the MongoDBHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'mongodb',
                    'options' => [
                        'maxNestingLevel' => 3, // optional : 0 means infinite nesting, the $record itself is level 1, $record['context'] is 2
                        'exceptionTraceAsString' => true, // optional : set to false to log exception traces as a sub documents instead of strings
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MongoDBFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/MongoDBFormatter.php)

### LogmaticFormatter

User to format log records to [Logmatic](http://logmatic.io/) messages, only useful for the
LogmaticHandler.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'logmatic',
                    'options' => [
                        'batchMode' => \Monolog\Formatter\LogmaticFormatter::BATCH_MODE_JSON, // Optional
                        'appendNewline' => true, // Optional
                        'ignoreEmptyContextAndExtra' => false, // Optional
                        'includeStacktraces' => false, // Optional
                        'dateFormat' => "c", // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [LogmaticFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LogmaticFormatter.php)

### FluentdFormatter

Serializes a log message to Fluentd unix socket protocol. Requires the json Extension.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'fluentd',
                    'options' => [
                        'levelTag' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [FluentdFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/FluentdFormatter.php)

### StreamFormatter

Formats the message using a symfony table.
Requires [StreamFormatter](https://github.com/mimmi20/monolog-streamformatter).

```php
<?php

return [
    'monolog' => [
        'default' => [
            'formatters' => [
                'myFormatterName' => [
                    'type' => 'stream',
                    'options' => [
                        'format' => "%message%", // Optional
                        'tableStyle' => 'box', // Optional
                        'dateFormat' => "c", // Optional : The format of the timestamp: one supported by DateTime::format
                        'allowInlineLineBreaks' => false, // Optional : Whether to allow inline line breaks in log entries
                        'includeStacktraces' => false, // Optional
                        'maxNormalizeDepth' => 9, // Optional
                        'maxNormalizeItemCount' => 1000, // Optional
                        'prettyPrint' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

## Processors

### PsrLogMessageProcessor

Processes a log record's message according to PSR-3 rules, replacing {foo} with the value from $context['foo'].

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'psrLogMessage',
                    'options' => [
                        'dateFormat' => "c", // Optional
                        'removeUsedContextFields' => false, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [PsrLogMessageProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/PsrLogMessageProcessor.php)

### IntrospectionProcessor

Adds the line/file/class/method from which the log call originated.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'introspection',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this processor will be triggered
                        'skipClassesPartials' => [], // Optional
                        'skipStackFramesCount' => 0, // Optional
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [IntrospectionProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/IntrospectionProcessor.php)

### WebProcessor

Adds the current request URI, request method and client IP to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'web',
                    'options' => [
                        'serverData' => 'my-service', // Optional: Array, object w/ ArrayAccess, or valid service name that provides access to the $_SERVER data
                        'extraFields' => [], // Optional: Field names and the related key inside $serverData to be added. If not provided it defaults to: url, ip, http_method, server, referrer
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [WebProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/WebProcessor.php)

### MemoryUsageProcessor

Adds the current memory usage to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'memoryUsage',
                    'options' => [
                        'realUsage' => true,
                        'useFormatting' => true,
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MemoryUsageProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/MemoryUsageProcessor.php)

### MemoryPeakUsageProcessor

Adds the peak memory usage to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'memoryPeak',
                    'options' => [
                        'realUsage' => true,
                        'useFormatting' => true,
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MemoryPeakUsageProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/MemoryPeakUsageProcessor.php)

### ProcessIdProcessor

Adds the process id to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'processId',
                    'options' => [], // No options
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [ProcessIdProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/ProcessIdProcessor.php)

### UidProcessor

Adds a unique identifier to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'uid',
                    'options' => [
                        'length' => 7, // Optional: The uid length. Must be an integer between 1 and 32
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [UidProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/UidProcessor.php)

### GitProcessor

Adds the current git branch and commit to a log record.

_Note:  Only works if the git executable is in your working path._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'git',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this processor will be triggered
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [GitProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/GitProcessor.php)

### MercurialProcessor

Adds the current hg branch and commit to a log record.

_Note:  Only works if the hg executable is in your working path._

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'mercurial',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this processor will be triggered
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [MercurialProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/MercurialProcessor.php)

### TagProcessor

Adds an array of predefined tags to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'tags',
                    'options' => [
                        'tags' => [], // Optional: Array of tags to add to records
                    ],
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [TagProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/TagProcessor.php)

### HostnameProcessor

Adds the current hostname to a log record.

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'hostname',
                    'options' => [], // No options
                ],
            ],
        ],
    ],
];
```

Monolog Docs: [HostnameProcessor](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Processor/HostnameProcessor.php)

### RequestHeaderProcessor

Adds Request Headers to a log record.
Requires [jk/monolog-request-header-processor](https://github.com/jk/monolog-request-header-processor)

```php
<?php

return [
    'monolog' => [
        'default' => [
            'processors' => [
                'myProcessorsName' => [
                    'type' => 'requestheader',
                    'options' => [
                        'level' => \Psr\Log\LogLevel::DEBUG, // Optional: The minimum logging level at which this processor will be triggered
                    ],
                ],
            ],
        ],
    ],
];
```

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
