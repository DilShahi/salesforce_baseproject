# Salesforce authentication

For salesforce authentication we will be using Salesforce CLI as we do not have access to generate $\textcolor{blue}{\text{CLIENT ID}}$ and $\textcolor{blue}{\text{CLIENT SECRET}}$.
So in order to install $\textcolor{red}{\text{Salesforce CLI}}$ we will use the following link to download:

- [Salesforce CLI](https://developer.salesforce.com/tools/salesforcecli)

## Configuration

In order to run any Salesforce command, we need to determine the Salesforce binary. So, for that we will use the following command:

$\textcolor{red}{\text{macOS/Linux:}}$

```
which sf
```

$\textcolor{red}{\text{Windows (PowerShell or CMD):}}$

```
where sf
```

Then it will return your Salesforce binary location. Then update your $\textcolor{blue}{\text{.env}}$ file as below:

```
SF_BINARY_LOCATION="/usr/local/bin/sf"
SF_ALIAS_NAME="okicom"
```

Then we will update our $\textcolor{blue}{\text{config/services.php}}$ file with this:

```
'salesforce' => [
    'binary_location' => env('SF_BINARY_LOCATION'),
    'alias_name' => env('SF_ALIAS_NAME'),
],
```

Then open your terminal and run the following command $\textcolor{blue}{\text{(each machine must do this once)}}$:

$\textcolor{red}{\text{macOS/Linux:}}$

```
export HOME="/path/to/your/project/storage/sf-home"
export SF_USE_GENERIC_UNIX_KEYCHAIN=true

sf org login web --alias okicom
```

$\textcolor{red}{\text{Windows PowerShell:}}$

```
$env:HOME="C:\path\to\your\project\storage\sf-home"
$env:SF_USE_GENERIC_UNIX_KEYCHAIN="true"

sf org login web --alias okicom
```

$\textcolor{red}{\text{Windows CMD:}}$

```
set HOME=C:\path\to\your\project\storage\sf-home
set SF_USE_GENERIC_UNIX_KEYCHAIN=true

sf org login web --alias okicom

```
