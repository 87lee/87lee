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
    public function fields()
    {
        
        return [
            'id','number','pwd','createTime'=>'create_time','updateTime'=>'update_time'
        ];
    }
    public function addOptions($post)
    {
        /*ALTER TABLE `ofo_bicycle`
        ADD COLUMN `create_time`  timestamp NOT NULL AFTER `pwd`,
        ADD COLUMN `update_time`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `create_time`;*/
        
        if (!empty($post['password']) && $post['number']) {
            $this->number = $post['number'];
            $this->pwd = $post['password'];
            $time = \Yii::$app->formatter->asDatetime(time());
            $this->create_time = $time;
            $this->update_time = $time;
            $one = $this->find()->where(['number'=>$post['number']])->one();
            if (!empty($one->id)) {
                $one = $this->findOne($one->id);
                $one->number = $post['number'];
                $one->pwd = $post['password'];
                $one->save();
            }else{
                $this->save();
            }
        }
        $num = 10;
        $data = ['result'=>'ok','numbers'=>[]];
        if (!empty($post['number'])) {
            $res = $this->find()->where(['number'=>$post['number']])->one();
            if (!empty($res)) {
                $data['numbers'][] = $res;
                $num = 9;
            }
        }

        $data['numbers'] = array_merge($data['numbers'],$this->find()->where(['!=','number',$post['number']])->orderBy('update_time desc')->limit($num)->all());
        
        return $data;
    }
}
