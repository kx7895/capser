<?php
namespace App\Service;

use App\Entity\Preference;
use App\Entity\User;
use App\Entity\UserPreference;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class UserPreferenceService {

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function get(User $user, string $setting): ?string {
        // Suche nach UserPreference
        $userPreference = $user->getUserPreferences()->filter(function($userPreference) use ($setting) {
            return $userPreference->getSetting() === $setting;
        })->first();

        if($userPreference) {
            return $userPreference->getValue();
        }

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        $preference = $preferenceRepository->findOneBy(['setting' => $setting]);

        return $preference ? $preference->getDefaultValue() : null;
    }

    public function set(User $user, string $setting, ?string $value): ?string {
        $userPreference = $user->getUserPreferences()->filter(function($userPreference) use ($setting) {
            return $userPreference->getSetting() === $setting;
        })->first();

        if($userPreference) {
            $userPreference->setValue($value);
            $userPreference->setUpdatedAt(new DateTimeImmutable());
        } else {
            $userPreference = new UserPreference();
            $userPreference->setUser($user);
            $userPreference->setSetting($setting);
            $userPreference->setValue($value);
            $userPreference->setUpdatedAt(new DateTimeImmutable());
            $this->entityManager->persist($userPreference);
            $user->getUserPreferences()->add($userPreference);
        }

        $this->entityManager->flush();

        return $value;
    }

    public function handle(User $user, string $setting, $value): ?string {
        if($value === '')
            // Wenn der Parameter explizit leer ist = in URL oder Form wurde explizit leer durch den Benutzer übergeben, speichere NULL und gib dies zurück
            return $this->set($user, $setting, null);
        elseif ($value === null)
            // Wenn der Parameter NULL ist, hat der Benutzer keinerlei Angabe (auch kein explizit leer) gemacht, schaue, ob es einen Wert gibt und gib diesen ggf. zurück, gibt ansonsten NULL zurück
            return $this->get($user, $setting);
        elseif(is_numeric($value) && $value > 0)
            return $this->set($user, $setting, $value);
        elseif(is_string($value) && $value <> '')
            return $this->set($user, $setting, $value);
        elseif(is_bool($value))
            return $this->set($user, $setting, $value);

        return null;
    }

}