# acra-php-mailer

Simple PHP based server side component for [ACRA](http://www.acra.ch) (crash reporting library for Android apps) which forwards all incoming crash reports to a defined mail address. The stack trace is additionally attached as an TXT file to allow easier retracing of [ProGuard](http://proguard.sourceforge.net/) obfuscated source code.



## Prerequsites

- A web server with PHP support
- ACRA added to your Android app



## Setup

### ACRA PHP Mailer

1. Edit the configuration at the top of the `acra.php` file. Set appropriate mail addresses there.

  ```
  // Configuration
  $to = 'your@mail.com';
  $from = 'your@mail.com';
  ```

2. Upload the `acra.php` file to your PHP supporting web server.

To prevent unauthorized access to the `acra.php` file you should password protect it, e.g. by using a `.htaccess` file if supported by your web server.


1. Edit the `.htpasswd` file and replace the username/password combination with your secret credentials. The username/password combination can be generated e.g. on [this](http://www.htaccesstools.com/htpasswd-generator/) website. You can also re-use an existing `.htpasswd` file if available.
2. Upload the `.htpasswd` file to your web server.

  > WARNING: For security reasons you should never upload a `.htpasswd` file to a folder accessible from the Internet!

3. Edit the `.htaccess` file  and set the correct path to the `.htpasswd` file above.

 ```
 AuthUserFile /path/to/your/.htpasswd
 ```
 
4. Upload the  `.htaccess` file to the same folder as the `acra.php` file.


### ACRA (Quick Start)

The full documentation of ACRA is available on [www.acra.ch](http://www.acra.ch), the following section is just a quick start to use ACRA together with the ACRA PHP Mailer.

1. Add ACRA as dependency to your app module Gradle file:

  ```
  dependencies {
      ... your other dependencies...
      compile "ch.acra:acra:4.7.0"
  }
  ```
2. Create or update an Application subclass and add ACRA to it:

  ```java
  @ReportsCrashes(
      formUri = "http://www.your.url/acra.php",
      formUriBasicAuthLogin = "your username", // optional
      formUriBasicAuthPassword = "your password",  // optional
      reportType = org.acra.sender.HttpSender.Type.JSON,
      sendReportsAtShutdown = false
  )
  public class MyApplication extends Application {
      @Override
      public void onCreate() {
          ACRA.init(this);
          super.onCreate();
      }
  }
  ```

3. Reference the new Application class in your Android Manifest:

  ```xml
  <application
      android:name=".MyApplication"
      android:icon="@mipmap/ic_launcher"
      android:label="@string/app_name">
      [...]
  ```

4. Obviously, your app will need the INTERNET permission:

  ```xml
  <uses-permission android:name="android.permission.INTERNET"/>
  ```

5. Update your ProGuard file to keep ACRA classes, all details can be found [here](https://github.com/ACRA/acra/wiki/ProGuard):

  ```
  -renamesourcefileattribute SourceFile
  -keepattributes SourceFile,LineNumberTable
  -keep class org.acra.** { *; }
  ```

6. Done, from now on you should receive a mail every time your app crashes!
