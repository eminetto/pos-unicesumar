<?php

namespace Application\Model;

 use Zend\InputFilter\InputFilter;
 use Zend\InputFilter\InputFilterAwareInterface;
 use Zend\InputFilter\InputFilterInterface;

 class Post implements InputFilterAwareInterface
 {
     public $id;
     public $title;
     public $description;
     public $post_date;
     public $inputFilter;

     //usado pelo TableGateway
     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->title = (!empty($data['title'])) ? $data['title'] : null;
         $this->description  = (!empty($data['description'])) ? $data['description'] : null;
         $this->post_date  = (!empty($data['post_date'])) ? $data['post_date'] : null;
     }

     // Exigido pela implementaÃ§Ã£o da interface InputFilterAwareInterface
     public function setInputFilter(InputFilterInterface $inputFilter)
     {
         throw new \Exception("NÃ£o usado");
     }

     /**
     * Configura os filtros dos campos da classe
     *
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
 
            $inputFilter->add(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            ));
 
            $inputFilter->add(array(
                'name'     => 'title',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));
 
            $inputFilter->add(array(
                'name'     => 'description',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ));
 
            $inputFilter->add(array(
                'name'     => 'post_date',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ));
 
            $this->inputFilter = $inputFilter;
        }
 
        return $this->inputFilter;
    }

    //necessÃ¡rio para o uso dos forms
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
 }