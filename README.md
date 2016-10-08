# tivars_lib [![Build Status](https://travis-ci.org/adriweb/tivars_lib.svg)](https://travis-ci.org/adriweb/tivars_lib)
A PHP "library" to interact with TI-Z80/eZ80 (82/83/84 series) calculators files (programs, lists, matrices...).

### How to use
Right now, the best documentation is [the tests file](tests.php) itself, which uses the main API methods.  
Basically, though, there are loading/saving/conversion (data->string, string->data) methods you just have to call.

**Example 1**: Here's how to read the source of TI-Basic program from an .8xp file and output it in HTML:
```php
$myPrgm = TIVarFile::loadFromFile('the/path/to/myProgram.8xp');
$basicSource = $myPrgm->getReadableContent(); // You can pass options like ['reindent' => true]...
echo '<pre><code>' . htmlentities($basicSource, ENT_QUOTES) . '</code></pre>';
```
**Example 2**: Here's how to create a TI-Basic program (output: .8xp file) from a string:
```php
$newPrgm = TIVarFile::createNew(TIVarType::createFromName('Program'));  // Create an empty "container" first
$newPrgm->setVarName('TEST');                                           // (also an optional parameter above)
$newPrgm->setContentFromString('ClrHome:Disp "Hello World!"');          // Set the var's content from a string
$newPrgm->saveVarToFile('path/to/output/directory/', 'myNewPrgrm');     // The extension added automatically
```

Several optional parameters for the functions are available. For instance, French is a supported input/output language for the program vartype, which is choosable with a boolean in an options array to pass.

_Note: The code throws exceptions for you to catch in case of trouble._

### Vartype handlers implementation: current status

| Vartype                   | data->string | string->data |
|---------------------------|:------------:|:------------:|
| Real                      |     **✓**    |     **✓**    |
| Real List                 |     **✓**    |     **✓**    |
| Matrix                    |     **✓**    |     **✓**    |
| Equation                  |     **✓**    |     **✓**    |
| String                    |     **✓**    |     **✓**    |
| Program                   |     **✓**    |     **✓**    |
| Protected Program         |     **✓**    |     **✓**    |
| Complex                   |     **✓**    |     **✓**    |
| Complex List              |     **✓**    |     **✓**    |
| Exact Complex Fraction    |     **✓**    |     **✗**    |
| Exact Real Radical        |     **✓**    |     **✗**    |
| Exact Complex Radical     |     **✓**    |     **✗**    |
| Exact Complex Pi          |     **✓**    |     **✗**    |
| Exact Complex Pi Fraction |     **✓**    |     **✗**    |
| Exact Real Pi             |     **✓**    |     **✗**    |
| Exact Real Pi Fraction    |     **✓**    |     **✗**    |

Note that some of the special varnames restrictions (for strings, matrices, list...) aren't implemented yet.

To this date, there are no plans to support other types (except maybe some fancy things with the image/picture vartypes...).  
Pull Requests are welcome, though :)
