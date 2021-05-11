<?php


namespace nemmo\attachments\models;


use yii\db\ActiveQuery;

class FileQuery extends ActiveQuery
{
    public string $class;

    public function prepare($builder)
    {
        if ($this->class !== null) {
            $this->andWhere(['class' => $this->class]);
        }
        return parent::prepare($builder);
    }

}