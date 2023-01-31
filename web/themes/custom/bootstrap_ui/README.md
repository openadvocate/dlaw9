# Project Theme Setup instructions    

## 1. Initial set up of drupal project theme       
These are steps to follow for developer who sets up the project theme the very first time    

### 1.1. Copy the latest starterkit available for the type of project. which should contain:    
- correct file organization structure    
- package.json file     
- gulp file       
- .gitignore file    

### 1.2. Check your node version     

```    
node -v
```   

### 1.3. Install packages on your local from the package.json    

```    
npm install

# grabs packages    
# this will also generate/update the startertheme's `package-lock.json` file    
```    

### 1.4. Test if gulp scprit is running succesfully    

```    
gulp watch

# should already be set up in starterkit    
```    

### 1.5. Troubleshoot if necesarry    
- Do not downgrade Gulp version, upgrade your node version instead    
- IF gulp watch failed -> modify node version as necesarry    

```
# Check your available node versions    
nvm ls    

# Check all available from nodejs.org    
nvm ls available    

# Grab a newer version if necesarry    
nvm install 12.14.1    

# Switch to the right version    
nvm use 12.14.1    

# Try gulp again    
gulp watch    

```    

### 1.6. Lock in node version, once Gulp watch runs sucessfully    

```    
#Lock in node version for the project    

node -v > .nvmrc    

```    

### 1.7. Push changes inscluding .nvmrc file containing the node version   
 

## 2. Subsequent setup by developers    

These are steps to follow for developer who want to use the theme and compile SASS, lint JS etc.    

### 2.1. Grab the same version of node that this project was set up with    


```    
npm use

# will grab the version that was set up initially for this project    
```    

### 2.2. Grab packages

```    
npm install

#alternatively if you want to keep package json    
```    
### 2.3. Start task runner    

```    
gulp watch

#star compiling SASS watch Js files etc.    
```    
