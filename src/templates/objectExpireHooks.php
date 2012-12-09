/**
 * This hook is called in case the object is about to expire.
 *
 * @return bool
 */
protected function preExpire()
{
    return true;
}

/**
 * This hook is called after the object has expired.
 *
 * @return void
 */
protected function postExpire()
{
}
