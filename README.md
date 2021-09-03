#KDWEB

## Symfony

Projet fil rouge pour tester les sujets suivants :

- [x] Pré-requis et initialisation d'un projet
- [x] La [CLI](https://symfony.com/download) de symfony
- [x] Le [maker bundle](https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html)
- [x] [Doctrine ORM](https://symfony.com/doc/current/doctrine.html)
- [x] [Association mapping](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/association-mapping.html) avec doctrine
- [x] Création d'une [commande](https://symfony.com/doc/current/console.html)
- [x] Implémenter un [système d'inscription](https://symfony.com/doc/current/doctrine/registration_form.html) avec le maker bundle
- [x] Protection du formulaire de login contre les attaques par brute force
- [x] Ajouter la fonctionnalité [remember me](https://symfony.com/doc/current/security/remember_me.html)
- [x] Permettre à l'utilisateur de modifier son mot de passe
- [x] Mise en place d'un captcha avec [HCaptcha](https://www.hcaptcha.com/)
- [x] [Personnalisation des pages d'erreurs](https://symfony.com/doc/current/controller/error_pages.html)

## Fixtures

- [x] [DataFixtures](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) et [alice](https://github.com/nelmio/alice)
- [x] [Fournisseur de données personnalisées](https://drib.tech/programming/symfony-4-alice-3-tutorial) avec alice
- [x] Gérer les [références](https://github.com/nelmio/alice/blob/master/doc/relations-handling.md) avec alice

## Security

- [x] Le système de [sécurité](https://symfony.com/doc/current/security.html) de Symfony
- [x] Le [UserChecker](https://symfony.com/doc/current/security/user_checkers.html)

## Formulaire

- [x] Le composant [form](https://symfony.com/doc/current/forms.html)
- [x] Protection [CSRF](https://symfony.com/doc/current/security/csrf.html) avec les formulaires
- [x] Creation d'un [formulaire sans class](https://symfony.com/doc/current/form/without_class.html)
- [x] Configuration du [empty_data](https://symfony.com/doc/current/form/use_empty_data.html) pour les formulaires
- [x] Les [types](https://symfony.com/doc/current/reference/forms/types.html) de champs
- [x] [Form Model Classes](https://symfonycasts.com/screencast/symfony-forms/form-dto) (DTO)
- [x] Les [contraintes](https://symfony.com/doc/current/reference/constraints.html) de validation
- [x] Contraintes de validation [personnalisées](https://symfony.com/doc/current/validation/custom_constraint.html)
- [x] [FormEvent](https://symfony.com/doc/current/form/events.html)
- [x] Le [paramConverter](https://symfony.com/bundles/SensioFrameworkExtraBundle/current/index.html)

## Compiler passes

- [x] Travailler avec les [Compiler passes](https://symfony.com/doc/current/service_container/compiler_passes.html) et les services tagger
- [x] Utilisation du [ServiceLocator](https://symfony.com/doc/current/service_container/service_subscribers_locators.html#using-service-locators-in-compiler-passes) dans les compiler passes
- [x] Obtenir et définir des [definitions de service](https://symfony.com/doc/current/service_container/definitions.html)

## Twig

- [x] Twig [documentation](https://twig.symfony.com/doc/3.x/)
- [x] Les [filtres](https://twig.symfony.com/doc/3.x/filters/index.html)

## Email

- [x] [maildev](https://www.npmjs.com/package/maildev)
- run mail dev cmd : ```maildev --hide-extensions STARTTLS```
- [x] [mailer](https://symfony.com/doc/current/mailer.html)
- [x] Utiliser un [template twig](https://symfony.com/doc/current/mailer.html#html-content) pour créer un email
- [x] [Intégration d'images](https://symfony.com/doc/current/mailer.html#mailer-twig-embedding-images) dans un e-mail

## Events

- [x] Les [listeners](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener)
- [x] Les [subscribers](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber)
- [x] [Authentication events](https://symfony.com/doc/current/components/security/authentication.html#authentication-events)
- [x] [LogoutEvent](https://symfony.com/blog/new-in-symfony-5-1-simpler-logout-customization)
- [x] [Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html)
- [x] [Doctrine Events](https://symfony.com/doc/current/doctrine/events.html)

## Logging

- [x] [LoggerInterface](https://www.php-fig.org/psr/psr-3/) PSR-3
- [x] Le [stockage](https://symfony.com/doc/current/logging.html#where-logs-are-stored) des log
- [x] [Limiter](https://symfony.com/doc/current/logging.html#how-to-rotate-your-log-files) la taille des fichiers de log
- [x] Basculer un [canal](https://symfony.com/doc/current/logging/channels_handlers.html#switching-a-channel-to-a-different-handler) vers un autre gestionnaire
- [x] Création de [canaux personnalisé](https://symfony.com/doc/current/logging/channels_handlers.html#configure-additional-channels-without-tagged-services)
- [x] [Câblage automatique](https://symfony.com/doc/current/logging/channels_handlers.html#how-to-autowire-logger-channels) des canaux

## Serializer

- [x] Le composant [serializer](https://symfony.com/doc/current/components/serializer.html)