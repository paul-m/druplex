; drupal-travis make file
; How to make make files:
; https://drupal.org/node/1006620

core = 7.x
api = 2

; Be specific with the version.
; Push changes only after testing.
projects[drupal][version] = 7.38

projects[] = features
