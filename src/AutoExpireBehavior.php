<?php

class AutoExpireBehavior extends Behavior
{
    protected $parameters = array(
        'column' => 'expires_at',
        'required' => 'true',
        'auto_delete' => 'false',
    );

    public function modifyTable()
    {
        $columnName = $this->getParameter('column');

        if (!$this->getTable()->hasColumn($columnName)) {
            $this->getTable()->addColumn(array(
                'name' => $columnName,
                'type' => 'TIMESTAMP',
                'required' => $this->getParameter('required'),
            ));
        }
    }

    /**
     * Add behavior to the postHydrate-hook of a model.
     *
     * @param ObjectBuilder $builder
     *
     * @return string
     */
    public function postHydrate($builder)
    {
        return $this->renderTemplate('objectPostHydrate');
    }

    /**
     * Add methods to the object this behavior is attached to.
     *
     * @param ObjectBuilder $builder
     *
     * @return string
     */
    public function objectMethods($builder)
    {
        $builder->declareClass('\DateTime');

        $script = ''
            . $this->renderTemplate('objectExpireHooks')
            . $this->renderTemplate('objectMethodExpire')
            . $this->renderTemplate('objectMethodIsExpired')
        ;

        return $script;
    }

    protected function getColumnGetter($parameter)
    {
        return 'get' . $this->getColumnForParameter($parameter)->getPhpName();
    }

    protected function getTemplateOptions()
    {
        $options = array_merge($this->getParameters(), array(
            'getExpiresAt' => $this->getColumnGetter('column'),
            'required' => $this->booleanValue($this->getParameter('required')),
            'auto_delete' => $this->booleanValue($this->getParameter('auto_delete')),
        ));

        return $options;
    }

    public function renderTemplate($filename, $vars = array(), $templateDir = '/templates/')
    {
        if (empty($vars)) {
            $vars = $this->getTemplateOptions();
        }

        return parent::renderTemplate($filename, $vars, $templateDir);
    }
}
