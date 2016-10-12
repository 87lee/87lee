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
    /*public $number;
    public $pwd;*/
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
            ['number','unique'],
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
    public function addOptions($post)
    {
        
        $this->number = $post['number'];
        $this->pwd = $post['pwd'];
        $one = $this->find()->where(['number'=>$post['number']])->one();
        if (!empty($one->id)) {
            $one = $this->findOne($one->id);
            $one->number = $post['number'];
            $one->pwd = $post['pwd'];
            return $one->save();
        }else{
            return $this->save();
        }
        
        
    }
}
