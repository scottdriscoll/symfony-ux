<?php

namespace App\Controller;

use Jbtronics\SettingsBundle\Manager\SettingsRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Jbtronics\SettingsBundle\Manager\SettingsManagerInterface;
use Jbtronics\SettingsBundle\Form\SettingsFormFactoryInterface;

class SettingsController extends AbstractController
{

    public function __construct(
        private readonly SettingsManagerInterface $settingsManager,
        private readonly SettingsFormFactoryInterface $settingsFormFactory,
        private readonly SettingsRegistryInterface $settingsRegistry,
    ) {}

    #[Route('/settings/list', name: 'app_settings_list')]
    public function list(): Response
    {
        return $this->render('settings/list.html.twig', [
            'settings' => array_keys($this->settingsRegistry->getSettingsClasses()),
        ]);
    }

    #[Route('/settings/show/{name}', name: 'app_settings')]
    public function index(Request $request, string $name): Response
    {
        try {
            $builder = $this->settingsFormFactory->createSettingsFormBuilder($name);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Setting ' . $name . ' not found');
        }

        $builder->add('submit', SubmitType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settingsManager->save();
        }

        return $this->render('settings/index.html.twig', [
            'name' => $name,
            'form' => $form,
        ]);
    }
}
