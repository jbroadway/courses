; <?php /*

[courses/learner/sidebar]

label = "Learners: Sidebar"
icon = lightbulb-o

[courses/learner/courses]

label = "Learners: My Courses"
icon = lightbulb-o

[courses/list]

label = "Courses: List"
icon = lightbulb-o

category[label] = "Category (optional)"
category[type] = select
category[callback] = "courses\Category::list_for_embed"

[courses/list]

label = "Courses: List"
icon = lightbulb-o

category[label] = "Category (optional)"
category[type] = select
category[callback] = "courses\Category::list_for_embed"

descriptions[label] = "Show descriptions"
descriptions[type] = select
descriptions[callback] = "courses\App::yes_no"

; */ ?>