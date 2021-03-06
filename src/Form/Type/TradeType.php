<?php

namespace App\Form\Type;

use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TradeType extends AbstractType
{
    public const PRICE_REGEX = '/^$|^\d{0,9}(\.\d{1,18})?$/';

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $symbolChoices = array_reduce(
            $this->manager->getRepository(Symbol::class)->findAll(),
            static function (array $carry, Symbol $item) {
                $carry[$item->getSymbol()] = $item->getSymbol();

                return $carry;
            },
            []
        );

        $builder
            ->add('stoploss', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => false,
                'constraints' => [
                    new Positive(),
                    new Regex([
                        'pattern' => self::PRICE_REGEX,
                        'normalizer' => static function ($value) {
                            return number_format($value, 18, '.', '');
                        },
                    ]),
                    new Callback(static function ($object, ExecutionContextInterface $context) {
                        if ($object === null || $object === '') {
                            return;
                        }

                        /** @var FormBuilderInterface $root */
                        $root = $context->getRoot();
                        /** @var Trade $data */
                        $data = $root->getData();

                        if ($object >= $data->getEntryLow()) {
                            $context->addViolation('stop loss needs to be lower than entry low');
                        }
                    }),
                ],
            ])
            ->add('symbol', ChoiceType::class, [
                'choices' => $symbolChoices,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Choice(['choices' => $symbolChoices]),
                ],
            ])
            ->add('quantity', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
            ])
            ->add('entryLow', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                    new Regex([
                        'pattern' => self::PRICE_REGEX,
                        'normalizer' => static function ($value) {
                            return number_format($value, 18, '.', '');
                        },
                    ]),
                ],
            ])
            ->add('entryHigh', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => false,
                'constraints' => [
                    new Positive(),
                    new Regex([
                        'pattern' => self::PRICE_REGEX,
                        'normalizer' => static function ($value) {
                            return number_format($value, 18, '.', '');
                        },
                    ]),
                    new Callback(static function ($object, ExecutionContextInterface $context) {
                        if ($object === null || $object === '') {
                            return;
                        }

                        /** @var FormBuilderInterface $root */
                        $root = $context->getRoot();
                        /** @var Trade $data */
                        $data = $root->getData();

                        if ($object <= $data->getEntryLow()) {
                            $context->addViolation('entry high needs to be higher than entry low');
                        }
                    }),
                ],
            ])
            ->add('takeProfits', CollectionType::class, [
                'entry_type' => TakeProfitType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trade::class,
            'method' => 'POST',
        ]);
    }
}
