<?php

namespace nemmo\attachments\models;

use nemmo\attachments\ModuleTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "attach_file".
 *
 * @property integer $id
 * @property string $name
 * @property string $model
 * @property integer $itemId
 * @property string $hash
 * @property integer $size
 * @property string $type
 * @property string $mime
 * @property string $class
 * @property string $customFields
 */
class File extends ActiveRecord
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('attachments')->tableName;
    }
    
    /**
     * @inheritDoc
     */
    public function fields()
    {
        return [
            'url'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'model', 'itemId', 'hash', 'size', 'mime'], 'required'],
            [['itemId', 'size'], 'integer'],
            [['name', 'model', 'hash', 'type', 'mime'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'model' => 'Model',
            'itemId' => 'Item ID',
            'hash' => 'Hash',
            'size' => 'Size',
            'type' => 'Type',
            'mime' => 'Mime',
            'class' => 'Class',
            'customFields' => 'Custom Fields',
        ];
    }

    public function getUrl()
    {
        return Url::to(['/attachments/file/download', 'id' => $this->id]);
    }

    public function getPath()
    {
        return $this->getModule()->getFilesDirPath($this->hash) . DIRECTORY_SEPARATOR . $this->hash . '.' . $this->type;
    }

    public function init()
    {
        $this->class = static::class;
        parent::init();
    }

    /**
     * @throws \Exception
     */
    public static function instantiate($row)
    {
        $className = $row['class'] ?? self::class;

        if (class_exists($className)) {
            if (isset($row['customFields'])) {
                if (!$customFields = unserialize($row['customFields'], ['allowed_classes' => true])) {
                    $customFields = [];
                }

                // We should filter out all fields that doesn't really exists in target class
                $keysExists = self::retrieveCustomFieldsForClass($className);
                $customFields = array_filter($customFields, static function($var) use ($keysExists) {
                    return in_array($var, $keysExists, true);
                }, ARRAY_FILTER_USE_KEY);
            } else { $customFields = []; }

            return new $className($customFields);
        }

        throw new \Exception("Class doesn't exists: " . $className);
    }

    protected static function retrieveCustomFieldsForClass(string $class = self::class): array
    {
        return array_keys(array_diff_key(
            get_class_vars($class),
            get_class_vars(self::class)
        ));
    }

    protected function serializeCustomFields(): string
    {
        $data = [];
        foreach(self::retrieveCustomFieldsForClass(static::class) as $customField) {
            $data[$customField] = $this->$customField;
        }
        return serialize($data);
    }


    public function beforeSave($insert): bool
    {
        $this->customFields = $this->serializeCustomFields();
        return parent::beforeSave($insert);
    }

}
