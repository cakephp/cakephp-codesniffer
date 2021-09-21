<?php
class Test {
    /**
     * Comment.
     *
     * @return $this
     */
    public function shouldReturnThis() {
    }

    /**
     * @return $this
     */
    public function shouldReturnThis2(): float|int {
    }

    /**
     * @return $this
     */
    public function shouldReturnThis3(): ?self {
    }

    /**
     * @return int
     */
    public function ignoreMe() {
    }
}

/**
 * @return this
 */
function ignoreMe() {
}
