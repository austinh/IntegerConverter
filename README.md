# IntegerConverter


 * Converts odd integers to strings and returns the sum of the common factors of even numbers.
 * Input must be integers, or decimals.
 * Even number's sum will ignore fractions/decimals. (Only use whole numbers for factors)
 * Negative numbers also be ignored for even numbers
 * Uses a modified Sieve of Erastonthenes algorithm
 
Example Code:

```
<?php
 // Assumes an autoloader is in place.
  include "IntegerConverter.php"

  $numbers = [0, '3', 0.51, 0.64, -10.2, 1007, 64, 80, -2, 4, 11.34];

  $converter = new Labroots\Utility\IntegerConverter($numbers);
  $converter->displayAsHTML();
?>
```

This should output:
```
Numbers Inputted: 0, 3, 0.51, 0.64, -10.2, 1007, 64, 80, -2, 4, 11.34
Odd Numbers:
Array (
  [3] => three
  [1007] => one thousand and seven
  [11.34] => eleven point thirty-four
)

Even Number Sum: 7 
```

As you can see, Numbers 0, 0.51, 0.64, -10.2, -2 were all ignroed (even numbers but were not whole/positive)
Odd numbers were 3, 1007, 11.34.
Even numbers were 64, 80, 4. Their common factors are 1, 2, 4. This sums to create 7.


You can also uses ->getValues() to retrieve the values of even sum and odd strings as an array.
