<?php
//IPP project
//part 1: parser.php
//author: David Rubý (xrubyd00)
$instructions = [
    "MOVE"              => [["var"], ["var", "int", "string", "bool", "nil"]],
    "CREATEFRAME"       => [],
    "PUSHFRAME"         => [],
    "POPFRAME"          => [],
    "DEFVAR"            => [["var"]],
    "CALL"              => [["label"]],
    "RETURN"            => [],
    "PUSHS"             => [["var", "int", "string", "bool", "nil"]],
    "POPS"              => [["var"]],
    "ADD"               => [["var"], ["var", "int"], ["var", "int"]],
    "SUB"               => [["var"], ["var", "int"], ["var", "int"]],
    "MUL"               => [["var"], ["var", "int"], ["var", "int"]],
    "IDIV"              => [["var"], ["var", "int"], ["var", "int"]],
    "LT"                => [["var"], ["var", "int", "bool", "string"], ["var", "int", "bool", "string"]],
    "GT"                => [["var"], ["var", "int", "bool", "string"], ["var", "int", "bool", "string"]],
    "EQ"                => [["var"], ["var", "int", "bool", "string"], ["var", "int", "bool", "string"]],
    "AND"               => [["var"], ["var", "bool"], ["var", "bool"]],
    "OR"                => [["var"], ["var", "bool"], ["var", "bool"]],
    "NOT"               => [["var"], ["var", "bool"]],
    "INT2CHAR"          => [["var"], ["var", "int"]],
    "STRI2INT"          => [["var"], ["var", "string"], ["var", "int"]],
    "READ"              => [["var"], ["type"]],
    "WRITE"             => [["var", "int", "string", "bool", "nil"]],
    "CONCAT"            => [["var"], ["var", "string"], ["var", "string"]],
    "STRLEN"            => [["var"], ["var", "string"]],
    "GETCHAR"           => [["var"], ["var", "string"], ["var", "int"]],
    "SETCHAR"           => [["var"], ["var", "int"], ["var", "string"]],
    "TYPE"              => [["var"], ["var", "int", "string", "bool", "nil"]],
    "LABEL"             => [["label"]],
    "JUMP"              => [["label"]],
    "JUMPIFEQ"          => [["label"], ["var", "int", "string", "bool", "nil"], ["var", "int", "string", "bool", "nil"]],
    "JUMPIFNEQ"         => [["label"], ["var", "int", "string", "bool", "nil"], ["var", "int", "string", "bool", "nil"]],
    "EXIT"              => [["var", "int"]],
    "DPRINT"            => [["var", "int", "string", "bool", "nil"]],
    "BREAK"             => []
];

$counter = 1;
$xml_header = '<?xml version="1.0" encoding="UTF-8"?><program></program>';
$xml = new SimpleXMLElement($xml_header);
$xml->addAttribute('language', 'IPPcode20');

$first_line = true;

foreach($argv as $argument){
    if(($argument == "-help") || ($argument == "--help"))
    print_help();
}

while($line = fgets(STDIN)){

    //ignoring comments
    $line = preg_replace("/#.*/", "", $line);

    //ignoring blank lines
    if($line == "\n"){
        continue;
    }

    //getting instruction
    $line = trim($line);
    $instruction = explode(' ', $line);

    //checking the header
    if($first_line){
        $line = strtolower($line);
        if(preg_match("/^\.ippcode20$/", $line)){
            $first_line = false;
            continue;
        }
        else{
            exit(21);;
        }
    }




    //exiting, if unvalid instruction is found
    if(!array_key_exists(strtoupper($instruction[0]), $instructions)){
        exit(22);
    }

    //checking, if number of arguments is the same as requested number of arguments
    if(count($instruction) -1 != count($instructions[strtoupper($instruction[0])])){
        exit(23);
    }

    $opcode = $xml->addChild('instruction');
    $opcode->addAttribute('order', $GLOBALS["counter"]++);
    $opcode->addAttribute('opcode', strtoupper($instruction[0]));

    $arg = $instructions[strtoupper($instruction[0])];

    //if instruction has any arguments
    if(count($instruction) - 1 >= 1) {
        //for every argument
        for ($i = 1; $i < count($instruction); $i++) {
            $var_ok = false;
            $int_ok = false;
            $string_ok = false;
            $bool_ok = false;
            $nil_ok = false;
            $type_ok = false;
            $label_ok = false;
            //foreach possibility in every argument
            foreach ($arg[$i - 1] as $what_to_check) {
                switch ($what_to_check) {
                    case "var":
                        $var_ok = check_var($opcode, $instruction[$i], $i);
                        break;
                    case "int":
                        $int_ok = check_int($opcode, $instruction[$i], $i);
                        break;
                    case "string":
                        $string_ok = check_string($opcode, $instruction[$i], $i);
                        break;
                    case "bool":
                        $bool_ok = check_bool($opcode, $instruction[$i], $i);
                        break;
                    case "nil":
                        $nil_ok = check_nil($opcode, $instruction[$i], $i);
                        break;
                    case "type":
                        $type_ok = check_type($opcode, $instruction[$i], $i);
                        break;
                    case "label":
                        $label_ok = check_label($opcode, $instruction[$i], $i);
                        break;
                    default:
                        exit(23);
                }
            }
            //if one of the checking was alright, XML was generated...
            //if none of them were OK, error occures.
            if (!($var_ok || $int_ok || $string_ok || $bool_ok || $nil_ok || $type_ok || $label_ok)) {
                exit(23);
            }
        }
    }
}

echo $xml->asXML();

//functions that check, if argument is correct and generate XML record

function check_var($opcode, $instruction, $arg_no){
    if(preg_match("/^(LF|GF|TF)@[_\-$&%*!?A-Ža-ž][_\-$&%*!?A-Ža-ž0-9]+$/", $instruction)) {
        $arg = $opcode->addChild('arg'.$arg_no, $instruction);
        $arg->addAttribute('type', 'var');
        return true;
    }else{return false;}
}

function check_int($opcode, $instruction, $arg_no){
    if(preg_match("/^int@([+-]?\d*)$/", $instruction)){
        $arg = $opcode->addChild('arg'.$arg_no, substr($instruction, 4));
        $arg->addAttribute('type', 'int');
        return true;
    }else{return false;}
}

function check_string($opcode, $instruction, $arg_no){
    if(preg_match("/^string@([A-Ža-ž]|\\\\[0-9]{3})*$/", $instruction)){
        $arg = $opcode->addChild('arg'.$arg_no, substr($instruction, 7));
        $arg->addAttribute('type', 'string');
        return true;
    }else{return false;}
}

function check_bool($opcode, $instruction, $arg_no){
    if(preg_match("/^bool@(true|false)$/", $instruction)){
        $arg = $opcode->AddChild('arg'.$arg_no, substr($instruction, 5));
        $arg->addAttribute('type', 'bool');
        return true;
    }else{return false;}
}

function check_nil($opcode, $instruction, $arg_no){
    if(preg_match("/^nil@nil$/", $instruction)){
        $arg = $opcode->addChild('arg'.$arg_no, 'nil');
        $arg->addAttribute('type', 'nil');
        return true;
    }else{return false;}
}

function check_type($opcode, $instruction, $arg_no){
    if(preg_match("/^int|string|bool$/", $instruction)) {
        $arg = $opcode->addChild('arg'.$arg_no, $instruction);
        $arg->addAttribute('type', 'type');
        return true;
    }else{return false;}
}

function check_label($opcode, $instruction, $arg_no){
    if(preg_match("/^[_\-$&%*!?A-Ža-ž][_\-$&%*!?A-Ža-ž0-9]+$/", $instruction)) {
        $arg = $opcode->addChild('arg'.$arg_no, $instruction);
        $arg->addAttribute('type', 'label');
        return true;
    }else{return false;}
}

function print_help(){
    echo "help\n";
}
?>