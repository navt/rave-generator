# rave-generator
Text generator based on Markov chains.<br>
In simple language: it is assumed that all the words of a particular text are 
random variables. And if the current word is known, then the next word can be 
generated from a certain limited set of words. Each word from such a set has some 
probability of falling out, and the sum of the probabilities of falling out of 
all the words of set 1. Thus, knowing the current word, we “roll the dice” and 
randomly select the next one from the set. And so on. This is the most primitive 
case of Markov chains.<br>
The essence of the algorithm is to build from the available text a map of all words 
of the text and sets of continuations corresponding to these words. From such a 
card, random text (nonsense) is randomly generated.<br>
There are several thousand implementations of Markov chains for different situations 
only on github.com, mostly in python, but I needed a PHP class “tailored” for my 
tasks: flexible selection of the source of the text, choice of the range of words 
in the sentence and the number of sentences.<br>
The class was intended to generate test material. Tested only on texts in Russian.
An example of using the class in `sg.php`.
