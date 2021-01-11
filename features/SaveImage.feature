Feature: Store image
  In order to save an image
  As a user
  I need to run the save CLI command
  passing it the local path of the image

  Rules:
  - The local path should point to an existing file
  - The file's mime type should be an image

  Scenario: Saving an valid image on the file system
    Given "valid_image.jpg" is a local file
    When I run the save command with argument "valid_image.jpg"
    Then the application displays a valid image path
    And the app's status code is 0

  Scenario: Saving an non-existing image on the file system
    Given "does_not_exist.jpg" is a non existing image
    When I run the save command with argument "does_not_exist.jpg"
    Then the application displays "Error: image not found"
    And the app's status code is 1

  Scenario: Saving an image with invalid type on the file system
    Given "invalid_file_type.txt" is a local file
    When I run the save command with argument "invalid_file_type.txt"
    Then the application displays "Error: this file type is not supported"
    And the app's status code is 1
