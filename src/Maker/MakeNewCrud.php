<?php

namespace TwinElements\CrudBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\MakerBundle\Validator;
use Doctrine\Common\Inflector\Inflector as LegacyInflector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Władysław Dudko <w.dudko@jellinek.pl>
 */
final class MakeNewCrud extends AbstractMaker
{
    private $doctrineHelper;
    private $projectDir;

    public function __construct(DoctrineHelper $doctrineHelper, string $projectDir)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->projectDir = $projectDir;
    }

    public static function getCommandName(): string
    {
        return 'make:new_crud';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates CRUD for Doctrine entity class')
            ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())));

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');

            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );

        $relativeEntityName = $entityClassDetails->getRelativeName();
        $relativeEntityNameWithoutSuffix = $entityClassDetails->getRelativeNameWithoutSuffix();
        if (strstr($entityClassDetails->getRelativeNameWithoutSuffix(), "\\")) {
            $explodedName = explode('\\', $relativeEntityNameWithoutSuffix);
            $relativeEntityNameWithoutSuffix = end($explodedName);
        }


        /**
         * @var EntityDetails $entityDoctrineDetails
         */
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());

        $availableInterfaces = new AvailableEntityInterfaces($entityClassDetails->getFullName(), $this->doctrineHelper);

        $repositoryVars = [];


        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\' . $entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );

            $repositoryVars = [
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),
                'repository_var' => lcfirst($this->singularize($repositoryClassDetails->getShortName())),
            ];
        }

        $controllerClassDetails = $generator->createClassNameDetails(
            $relativeEntityNameWithoutSuffix . 'Controller',
            'Controller\\Admin\\',
            'Controller'
        );

        $iter = 0;
        do {
            $formClassDetails = $generator->createClassNameDetails(
                $relativeEntityNameWithoutSuffix . ($iter ?: '') . 'Type',
                'Form\\Admin\\',
                'Type'
            );
            ++$iter;
        } while (class_exists($formClassDetails->getFullName()));

        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));

        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);

        $routeName = Str::asRouteName($controllerClassDetails->getRelativeNameWithoutSuffix());
        $templatesPath = Str::asFilePath($controllerClassDetails->getRelativeNameWithoutSuffix());

        // ROLES
        $roleClassDetails = $generator->createClassNameDetails(
            $relativeEntityNameWithoutSuffix . 'Roles',
            'Security\\',
            'Roles'
        );

        $generator->generateClass(
            $roleClassDetails->getFullName(),
            __DIR__ . '/../templates/skeleton/security/Roles.tpl.php',
            [
                'bounded_full_class_name' => $entityClassDetails->getFullName(),
                'bounded_class_name' => $entityClassDetails->getShortName(),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_var_singular' => $entityVarSingular,
            ]
        );

        $servicesYaml = Yaml::parseFile($this->projectDir . '/config/services.yaml');

        $rolesYaml = [
            'tags' => [
                [
                    'name' => 'twin_elements.role',
                    'priority' => 0
                ]
            ]
        ];

        $servicesYaml['services']["App\Security\\" . $entityClassDetails->getShortName() . "Roles"] = $rolesYaml;
        file_put_contents($this->projectDir . '/config/services.yaml', Yaml::dump($servicesYaml, 4));
        // END ROLES
        // VOTER
        $voterClassDetails = $generator->createClassNameDetails(
            $relativeEntityNameWithoutSuffix . 'Voter',
            'Security\\',
            'Voter'
        );

        $generator->generateClass(
            $voterClassDetails->getFullName(),
            __DIR__ . '/../templates/skeleton/security/Voter.tpl.php',
            [
                'bounded_full_class_name' => $entityClassDetails->getFullName(),
                'bounded_class_name' => $entityClassDetails->getShortName(),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_var_singular' => $entityVarSingular,
                'role_full_class_name' => $roleClassDetails->getFullName(),
                'role_short_class_name' => $roleClassDetails->getShortName()
            ]
        );
        // END VOTER
        // CONTROLLER
        $generator->generateController(
            $controllerClassDetails->getFullName(),
            __DIR__ . '/../templates/skeleton/crud/controller/Controller.tpl.php',
            array_merge([
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'form_full_class_name' => $formClassDetails->getFullName(),
                'form_class_name' => $formClassDetails->getShortName(),
                'route_path' => Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $routeName,
                'templates_path' => $templatesPath,
                'entity_var_plural' => $entityVarPlural,
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'availableInterfaces' => $availableInterfaces,
                'voter_full_name_class' => $voterClassDetails->getFullName(),
                'voter_short_name_class' => $voterClassDetails->getShortName()
            ],
                $repositoryVars
            )
        );
        // END CONTROLLER
        // TYPE
        $generator->generateClass(
            $formClassDetails->getFullName(),
            __DIR__ . '/../templates/skeleton/form/Type.tpl.php',
            [
                'bounded_full_class_name' => $entityClassDetails->getFullName(),
                'bounded_class_name' => $entityClassDetails->getShortName(),
                'form_fields' => $entityDoctrineDetails->getFormFields(),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'availableInterfaces' => $availableInterfaces,
                'entity_var_singular' => $entityVarSingular,
                'entity_twig_var_singular' => $entityTwigVarSingular,
            ]
        );
        // END TYPE

        $templates = [
            'edit' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'route_name' => $routeName,
                'availableInterfaces' => $availableInterfaces,
            ],
            'index' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $routeName,
                'availableInterfaces' => $availableInterfaces
            ],
            'new' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'route_name' => $routeName,
                'entity_twig_var_singular' => $entityTwigVarSingular,
            ]
        ];

        foreach ($templates as $template => $variables) {
            $generator->generateTemplate(
                'admin/' . $templatesPath . '/' . $template . '.html.twig',
                __DIR__ . '/../templates/skeleton/crud/templates/' . $template . '.tpl.php',
                $variables
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(sprintf('Next: Check your new CRUD by going to <fg=yellow>%s/</>', Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix())));
    }

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }

    private function pluralize(string $word): string
    {
        return LegacyInflector::pluralize($word);
    }

    private function singularize(string $word): string
    {
        return LegacyInflector::singularize($word);
    }
}
