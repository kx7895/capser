<?php

namespace App\Form\Field;

use App\Entity\Principal;
use App\Entity\User;
use App\Repository\PrincipalRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrincipalFieldType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {}

    private function getAllowedPrincipals(): Collection
    {
        /** @var User $user */
        $user = $this->security->getUser();
        return $user->getPrincipals();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $allowedPrincipals = $this->getAllowedPrincipals();

        $resolver->setDefaults([
            'row_attr' => [
                'class' => 'form-floating',
            ],
            'class' => Principal::class,
            'query_builder' => function (PrincipalRepository $repository) use ($allowedPrincipals) {
                return $repository->createQueryBuilder('principal')
                    ->andWhere('principal IN (:principals)')
                    ->setParameter('principals', $allowedPrincipals)
                    ->orderBy('principal.name', 'ASC');
            },
            'choice_label' => function (Principal $principal) {
                return $principal->getName().($principal->getShortName() ? ' ('.$principal->getShortName().')' : '');
            },
            'label' => 'Mandant <span class="text-danger">*</span>',
            'label_html' => true,
            'placeholder' => 'Bitte wählen...',
            'autocomplete' => true,
            'tom_select_options' => [
                'hideSelected' => true,
                'plugins' => [
                    'dropdown_input',
                    'clear_button',
                ],
            ],
            'required' => true,
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}