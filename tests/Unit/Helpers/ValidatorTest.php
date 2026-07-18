<?php
namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use Validator;

class ValidatorTest extends TestCase
{
    public function testRequiredValidation()
    {
        $data = ['name' => 'John', 'email' => ''];
        $validator = new Validator($data);
        $validator->required(['name', 'email']);
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors());
    }
    
    public function testEmailValidation()
    {
        $data = ['email' => 'invalid-email'];
        $validator = new Validator($data);
        $validator->email('email');
        
        $this->assertTrue($validator->fails());
        
        $data2 = ['email' => 'valid@example.com'];
        $validator2 = new Validator($data2);
        $validator2->email('email');
        
        $this->assertTrue($validator2->passes());
    }
    
    public function testMinLengthValidation()
    {
        $data = ['password' => '123'];
        $validator = new Validator($data);
        $validator->min('password', 8);
        
        $this->assertTrue($validator->fails());
        
        $data2 = ['password' => '12345678'];
        $validator2 = new Validator($data2);
        $validator2->min('password', 8);
        
        $this->assertTrue($validator2->passes());
    }
    
    public function testMaxLengthValidation()
    {
        $data = ['name' => 'This is a very long name that exceeds the maximum length'];
        $validator = new Validator($data);
        $validator->max('name', 50);
        
        $this->assertTrue($validator->fails());
    }
    
    public function testNumericValidation()
    {
        $data = ['age' => 'not-a-number'];
        $validator = new Validator($data);
        $validator->numeric('age');
        
        $this->assertTrue($validator->fails());
        
        $data2 = ['age' => '25'];
        $validator2 = new Validator($data2);
        $validator2->numeric('age');
        
        $this->assertTrue($validator2->passes());
    }
    
    public function testPasswordComplexityValidation()
    {
        $data = ['password' => 'simple'];
        $validator = new Validator($data);
        $validator->passwordComplexity('password');
        
        $this->assertTrue($validator->fails());
        
        $data2 = ['password' => 'Complex123!'];
        $validator2 = new Validator($data2);
        $validator2->passwordComplexity('password');
        
        $this->assertTrue($validator2->passes());
    }
    
    public function testMatchValidation()
    {
        $data = ['password' => 'password123', 'confirm_password' => 'different'];
        $validator = new Validator($data);
        $validator->match('password', 'confirm_password');
        
        $this->assertTrue($validator->fails());
        
        $data2 = ['password' => 'password123', 'confirm_password' => 'password123'];
        $validator2 = new Validator($data2);
        $validator2->match('password', 'confirm_password');
        
        $this->assertTrue($validator2->passes());
    }
}
