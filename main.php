<?php

use CsvTransform\Exception\{CasterException, FieldParseRuleException, ParseException, TransformException};
use CsvTransform\LanguageParser\{RuleFactory, QuickAndDirtyScriptParser};
use CsvTransform\Transform\Decorator\{Append, TitleCase, Replace, MathRound, UpperCase};
use CsvTransform\OutputWriter\{CsvWriter,JsonWriter};
use CsvTransform\TypeCaster\{DateCaster, IntegerCaster, NumberCaster, StringCaster};

require 'vendor/autoload.php';

/**
 * @return RuleFactory
 */
function buildRuleFactory(): RuleFactory
{
    $factory = new RuleFactory();
    $factory->registerDecorator(Append::class);
    $factory->registerDecorator(MathRound::class);
    $factory->registerDecorator(Replace::class);
    $factory->registerDecorator(TitleCase::class);
    $factory->registerDecorator(UpperCase::class);

    $factory->registerCaster(IntegerCaster::class);
    $factory->registerCaster(StringCaster::class);
    $factory->registerCaster(NumberCaster::class);
    $factory->registerCaster(DateCaster::class);

    return $factory;
}

/**
 * Open standard error, or /dev/null.
 *
 * @param bool $skipErrorOutput
 *
 * @return false|resource
 */
function openStderr(bool $skipErrorOutput)
{
    $stderr = fopen($skipErrorOutput ? '/dev/null' : 'php://stderr','wb');

    return $stderr;
}

/**
 * Returns TRUE if one of the listed flags is set.
 *
 * @param array $options
 * @param       ...$flagList
 *
 * @return bool
 */
function isOn(array $options, ...$flagList): bool
{
    foreach ($flagList as $flag) {
        if (array_key_exists($flag, $options))
            return true;
    }

    return false;
}

function main(array $argv): int
{
    $options = getopt('qht', [
        'help',
        'test',
        'list-transforms',
        'list-casts',
        'skip-first',
        'output:'
    ], $restIndex);

    if (isOn($options, 'h', 'help')) {
        echo /* @lang txt */ <<<HELP_TEXT
        Usage: php $argv[0] [-h|--help] transform_script csv_file
        
        Options:
            --help, -h
                Print this message and exit.
                
            --test, -t
                Performs a test compile on the transform_script only and exits.
                csv_file does not need to be defined if this flag is set. 
            
            --quiet -q
                Suppress error output.
            
            --list-transforms
                Prints a list of available transforms and their argument count.
                
            --list-casts
                Prints a list of available casts.
                
            --skip-first
                Indicates that the first input record should be skipped.  This is
                useful if the input file has a header row.  This also causes the CSV
                output to omit a header row.
                
            --output (csv|json)
                Designates the format of the output records.  Default is CSV.
        
        If csv_file is omitted input is read from stdin.  Records that error will be 
        skipped and the record will be written to stderr with a descriptive message.
         
        Script:
            transform_script should be a text file containing a description of the
            transformation that should be applied to each row.  The script is 
            composed of 2 blocks:
                - Parse
                - Output
            
            The Parse Block:
                The parse block creates variable to be used in the Output block.  
                It should take the following form:
                
                Parse:
                    {columnNumber} AS variable_name
                    {columnNumber} AS {regular expression (PCRE)}
                
                Column numbers are 0-indexed integers.  Variable names are lower case 
                alpha-numeric strings that start with a letter or _.
                
                Lines parsed as a regular expression will create varaibles for any named
                capture groups.
                
            The Output Stanza:
                The output block uses the variables created by the parse block and
                defines output fields as transformations on those varaibles.  It should
                take the following form:
                
                Output:
                    {cast} {Field name}: {TRANSFORMATION CHAIN}
                    
                Cast:
                    Cast is a final transform and validation applied before output.  It acts both as a formatter
                    and as a validation:
                        # This would error as there is no way to cast that string to an integer.
                        int Field name: "abcde"
                        
                        # This is fine
                        int Field name: "1234"
                  
                Field Name:
                    Any string of characters except for a ":" as this is the terminator.
                
                Transform chain:
                    These take the following form:
                        {value} [ | {TRANSFORM} [{args}...] ...] 
                    
                Values can be either a literal string defined in double quotes, or a variable.
                
                The + character is short-hand for the APPEND transform so:
                    "string" + variable
                is the same as:
                    "string" | APPEND variable
                    
                Transforms are applied from left to right, much like pipes on the command line.
                For this reason it is recommended to only use the "+" alias for APPEND at the
                start of the chain, as it can get confusing farther down:
                    # The output of this is "a-b end", though some would expect "a-endb"
                    "a b" | REPLACE " " "-" + " end"
                    
                Comments:
                    As demonstrated in the above example, blank lines, and lines whose first 
                    non-whitespace character is "#" are ignored.
           
            EXAMPLE:
            
                Parse:
                    0 AS id
                    1 AS /^(?<company>[^-]+)-(?<product_id>.+)$/
                    2 AS unit_cost
                    3 AS unit_qty
                Output:
                    int    ProductId: id
                    str Manufacturer: "front_" + company + "_Josiah" | REPLACE "_" " " | TITLE_CASE
                    str Product Code: product_id
                    str     Cost/per: unit_cost + "/" + unit_qty
                    num   Round test: unit_cost | M_ROUND "1" | APPEND "25"
                    
            Input record:
                1002,wiley coyote-D34,3000.59,7
                
            Result:
                ProductId,Manufacturer,Product Code,Cost/per,Round test
                1002,Front Wiley Coyote Josiah,D34,3000.59/7,3000.625
        
        HELP_TEXT;

        return 0;
    }

    $stderr = openStderr(($options['q'] ?? $options['quiet'] ?? null) === false);

    $factory = buildRuleFactory();

    if (isOn($options, 'list-transforms')) {
        echo implode("\n", $factory->getRegistredTransforms()), "\n";

        return 0;
    } elseif (isOn($options, 'list-casts')) {
        echo implode("\n", $factory->getRegistredCasts()), "\n";

        return 0;
    }

    $parser = new QuickAndDirtyScriptParser($factory);


    $scriptFile = $argv[$restIndex] ?? null;
    if (!$scriptFile) {
        fprintf($stderr, "Missing script file!\n");

        return 1;
    } elseif (($script = file_get_contents($scriptFile)) === false) {
        fprintf($stderr, "Unable to read script file '$scriptFile'.\n");

        return 1;
    }

    try {
        $program = $parser->parse($script);
    } catch (ParseException $exception) {
        fprintf($stderr, "{$exception->getMessage()}\n");

        return 1;
    }

    if (isOn($options, 't', 'test')) {
        echo "Script compiled successfully.\n";

        return 0;
    }

    $inputFile = $argv[$restIndex + 1] ?? 'php://stdin';
    if (($inputHnd = fopen($inputFile, 'rb')) === false) {
        fprintf($stderr, "Unable to open input file '$inputFile'.\n");

        return 1;
    }

    $outputHnd = fopen('php://stdout', 'wb');

    $output = $options['output'] ?? 'csv';
    $writer = match ($output) {
        'csv'  => new CsvWriter($outputHnd),
        'json' => new JsonWriter($outputHnd),
        default => null
    };

    if ($writer === null) {
        fprintf($stderr, "Unknown output type '$output'\n");

        return 1;
    }

    if (isOn($options, 'skip-first')) {
        fgets($inputHnd);  // Consume a line.
    }
    
    while(($line = fgets($inputHnd)) !== false) {
        try {
            $writer->write(
                $program->processRecord(
                    str_getcsv(rtrim($line, "\n"))
                )
            );
        } catch (CasterException|FieldParseRuleException|TransformException $exception) {
            fprintf($stderr, "{$exception->getMessage()}\n$line\n");
        }
    }


    return 0;
}

exit(main($_SERVER['argv']));
