<?php

namespace app\collectiveWeiXin\models;

use Yii;

/**
 * This is the model class for table "ofo_bicycle".
 *
 * @property string $id
 * @property integer $number
 * @property integer $pwd
 */
class OfoBicycle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ofo_bicycle';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'pwd'], 'required'],
            [['number', 'pwd'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Number',
            'pwd' => 'Pwd',
        ];
    }
}
