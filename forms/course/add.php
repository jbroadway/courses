; <?php /*

[title]

not empty = 1

[summary]

not empty = 1
length = "1-255"

[category]

type = numeric
gte = 1

[price]

callback = "courses\Rules::price_is_set"

; */ ?>