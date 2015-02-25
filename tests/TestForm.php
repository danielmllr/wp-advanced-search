<?php
namespace WPAS;
require_once(dirname(__DIR__).'/src/Input.php');
require_once(dirname(__DIR__).'/src/Form.php');
require_once(dirname(__DIR__).'/src/FormMethod.php');
require_once(dirname(__DIR__).'/src/ValidationException.php');

class TestForm extends \PHPUnit_Framework_TestCase {

    public function testCanGetAttributes() {
        $args = array(  
                        'action' => 'http://google.com',
                        'method' => 'GET',
                        'id' => 'my_id',
                        'name' => 'some_name',
                        'class' => array('form-class') );

        $form = new Form($args);
        $this->assertEquals($form->getAction(), 'http://google.com');
        $this->assertEquals($form->getMethod(), 'GET');
        $this->assertEquals($form->getID(), 'my_id');
        $this->assertEquals($form->getName(), 'some_name');
        $this->assertEquals($form->getClass(), array('form-class'));

    }

    public function testCanAddInput() {
        $args = array(
            'action' => 'http://google.com',
            'method' => 'GET',
            'id' => 'my_id',
            'name' => 'some_name',
            'class' => array('form-class') );

        $form = new Form($args);
        $input_args = array('field_type' => 'meta_key', 'format' => 'checkbox', 'values' => array('one', 'two'));
        $input = new Input("myinput", $input_args);

        $form->addInput($input);
    }

    public function testCanGetInput() {
        $args = array(
            'action' => 'http://google.com',
            'method' => 'GET',
            'id' => 'my_id',
            'name' => 'some_name',
            'class' => array('form-class') );

        $form = new Form($args);
        $input_args = array('field_type' => 'meta_key', 'format' => 'checkbox', 'values' => array('one', 'two'));
        $input = new Input("myinput", $input_args);

        $form->addInput($input);
        $inputs = $form->getInputs();
        $this->assertTrue(is_array($inputs) && count($inputs) == 1);
        $this->assertTrue($inputs[0] instanceof Input);
        $this->assertTrue($inputs[0]->getInputName() == "myinput");
    }

    public function testToHTML() {
        $args = array(
            'action' => 'http://google.com',
            'method' => 'GET',
            'id' => 'my_id',
            'name' => 'some_name',
            'class' => array('form-class') );

        $form = new Form($args);

        $input_args = array('field_type' => 'meta_key', 'format' => 'checkbox', 'values' => array('one', 'two'));
        $input = new Input("myinput", $input_args);
        $form->addInput($input);

        $input_args = array('field_type' => 'search', 'format' => 'text', 'placeholder' => 'Enter keywords...');
        $input = new Input("myinput", $input_args);
        $form->addInput($input);

        $this->assertTrue(is_string($form->toHTML()));
    }

    /**
     * @expectedException     WPAS\ValidationException
     */
    public function testFailsValidationWithBadAction() {
        $args = array( 'action' => 123 );
        $form = new Form($args);
    }

    /**
     * @expectedException     WPAS\ValidationException
     */
    public function testFailsValidationWithBadMethod() {
        $args = array( 'method' => 'BADMETHOD' );
        $form = new Form($args);
    }

    /**
     * @expectedException     WPAS\ValidationException
     */
    public function testFailsValidationWithBadID() {
        $args = array( 'id' => 123 );
        $form = new Form($args);
    }

    /**
     * @expectedException     WPAS\ValidationException
     */
    public function testFailsValidationWithBadName() {
        $args = array( 'name' => 123 );
        $form = new Form($args);
    }

    /**
     * @expectedException     WPAS\ValidationException
     */
    public function testFailsValidationWithBadClass() {
        $args = array( 'class' => array(1,2,3) );
        $form = new Form($args);
    }

}