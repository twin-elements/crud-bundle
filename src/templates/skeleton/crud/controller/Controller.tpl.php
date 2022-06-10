<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
use TwinElements\Component\CrudLogger\CrudLogger;
use Symfony\Bundle\FrameworkBundle\Controller\<?= $parent_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TwinElements\Component\ResponseParameterBuilder\ResponseParameterBuilder;
use TwinElements\AdminBundle\Model\CrudControllerTrait;
use TwinElements\SortableBundle\SortableResponseParametersPreparer;
use <?= $voter_full_name_class; ?>;

/**
 * @Route("<?= $entity_twig_var_singular ?>")
 */
class <?= $class_name ?> extends <?= $parent_class_name; ?><?= "\n" ?>
{

    use CrudControllerTrait;

    /**
     * @Route("/", name="<?= $route_name ?>_index", methods="GET")
     */
    public function index(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    {
        $this->denyAccessUnlessGranted(<?= $voter_short_name_class; ?>::VIEW, new <?= $entity_class_name ?>());
        $<?= $entity_var_plural ?> =  $<?= $repository_var ?>->findIndexListItems($request->getLocale());

        $this->breadcrumbs->setItems([
            $this->adminTranslator->translate('<?= $entity_twig_var_singular ?>.<?= $entity_twig_var_plural ?>') => null
        ]);


        $responseParameters = new ResponseParameterBuilder();
        $responseParameters
            ->addParameter('<?= $entity_twig_var_plural ?>',$<?= $entity_var_plural ?>);

        SortableResponseParametersPreparer::prepare($responseParameters, <?= $entity_class_name ?>::class);

        return $this->render('admin/<?= $route_name ?>/index.html.twig', $responseParameters->getParameters());
    }


    /**
     * @Route("/new", name="<?= $route_name ?>_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        try {
            $this->denyAccessUnlessGranted(<?= $voter_short_name_class; ?>::FULL, new <?= $entity_class_name ?>());

            $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
            <?php if($availableInterfaces->isTranslatable()) : ?>
            $<?= $entity_var_singular ?>->setCurrentLocale($request->getLocale());
            <?php endif; ?>

            $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($<?= $entity_var_singular ?>);
                <?php if($availableInterfaces->isTranslatable()) : ?>
                $<?= $entity_var_singular ?>->mergeNewTranslations();
                <?php endif; ?>

                $em->flush();

                $this->crudLogger->createLog(<?= $entity_class_name ?>::class, CrudLogger::CreateAction, $<?= $entity_var_singular ?>->getId());

                $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;

                if ('save' === $form->getClickedButton()->getName()) {
                    return $this->redirectToRoute('<?= $route_name ?>_edit', array('id' => $<?= $entity_var_singular ?>->getId()));
                } else {
                    return $this->redirectToRoute('<?= $route_name ?>_index');
                }

            }

            $this->breadcrumbs->setItems([
                $this->adminTranslator->translate('<?= $entity_twig_var_singular ?>.<?= $entity_twig_var_plural ?>') => $this->generateUrl('<?= $route_name ?>_index'),
                $this->adminTranslator->translate('<?= $entity_twig_var_singular ?>.creating_a_new_<?= $entity_twig_var_singular ?>') => null
            ]);

            return $this->render('admin/<?= $route_name ?>/new.html.twig', [
                '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
                'form' => $form->createView(),
            ]);

        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());
            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
    }


    /**
     * @Route("/{<?= $entity_identifier ?>}/edit", name="<?= $route_name ?>_edit", methods="GET|POST")
     */
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        try {
            $this->denyAccessUnlessGranted(<?= $voter_short_name_class; ?>::EDIT, new <?= $entity_class_name ?>());

            $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
            $form->handleRequest($request);

            $deleteForm = $this->createDeleteForm($<?= $entity_var_singular ?>);

            if ($form->isSubmitted() && $form->isValid()) {

                    <?php if($availableInterfaces->isTranslatable()) : ?>
                    $<?= $entity_var_singular ?>->mergeNewTranslations();
                    <?php endif; ?>

                    $this->getDoctrine()->getManager()->flush();
                    $this->crudLogger->createLog(<?= $entity_class_name ?>::class, CrudLogger::EditAction, $<?= $entity_var_singular ?>->getId());

                    $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;


                if ('save' === $form->getClickedButton()->getName()) {
                    return $this->redirectToRoute('<?= $route_name ?>_edit', ['<?= $entity_identifier ?>' => $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>()]);
                } else {
                    return $this->redirectToRoute('<?= $route_name ?>_index');
                }
            }

            $this->breadcrumbs->setItems([
                $this->adminTranslator->translate('<?= $entity_twig_var_singular ?>.<?= $entity_twig_var_plural ?>') => $this->generateUrl('<?= $route_name ?>_index'),
                $<?= $entity_var_singular ?>->getTitle() => null
            ]);

            return $this->render('admin/<?= $route_name ?>/edit.html.twig', [
                'entity' => $<?= $entity_var_singular ?>,
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
            ]);
        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());
            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
     */
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $this->denyAccessUnlessGranted(<?= $voter_short_name_class; ?>::FULL, new <?= $entity_class_name ?>());

        $form = $this->createDeleteForm($<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $id = $<?= $entity_var_singular ?>->getId();

                $em = $this->getDoctrine()->getManager();
                $em->remove($<?= $entity_var_singular ?>);
                $em->flush();

                $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
                $this->crudLogger->createLog(<?= $entity_class_name ?>::class, CrudLogger::DeleteAction, $id);

            }catch (\Exception $exception){
                $this->flashes->errorMessage($exception->getMessage());
            }
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }


    private function createDeleteForm(<?= $entity_class_name ?> $<?= $entity_var_singular ?>)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('<?= $route_name ?>_delete', array('id' => $<?= $entity_var_singular ?>->getId())))
        ->setMethod('DELETE')
        ->getForm();
    }
}
