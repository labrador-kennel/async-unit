<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Model;


trait CanBeDisabledTrait {

    private DisabledDeterminator $disabledDeterminator;
    private ?string $disabledReason = null;

    public function setDisabledDeterminator(DisabledDeterminator $disabledDeterminator) {
        $this->disabledDeterminator = $disabledDeterminator;
    }

    public function getDisabledDeterminator() : DisabledDeterminator {
        return isset($this->disabledDeterminator) ? $this->disabledDeterminator : $this->getDefaultDisabledDeterminator();
    }

    private function getDefaultDisabledDeterminator() : DisabledDeterminator {
        return new class implements DisabledDeterminator {

            public function isDisabled(): bool {
                return false;
            }

            public function getReason(): ?string {
                return null;
            }
        };
    }

}