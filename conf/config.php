; <?php /*

[Courses]

; A virtual page title to include in the navigation tree.
title = Courses

; Whether to include a virtual page in the navigation tree
; (see Tools > Navigation).
include_in_nav = On

; The public app name. Will appear as the page title at /courses.
public_name = Courses

; The layout to use for listings and other pages.
layout = default

; The layout to use for course content pages.
course_layout = default

; Enable/disable comments at the foot of course pages.
comments = On

; The payment handler for paid courses.
payment_handler = ""

; A callback to check for available discounts for the current user's
; membership type, specified as a percentage discount.
discount_callback = ""

; A callback to check whether an "invoice me" option should be available
; for course payments, allowing users to enter immediately and admins
; to receive a notice of registration that they will manually invoice for.
allow_invoice_callback = ""

[Admin]

handler = courses/admin
name = Courses
install = courses/install
upgrade = courses/upgrade
version = 0.9.4-beta

; */ ?>