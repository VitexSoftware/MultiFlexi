JENKINS_URI = https://jenkins.proxy.spojenet.cz/

prepare:
	sudo wget -c ${JENKINS_URI}/jnlpJars/jenkins-cli.jar -O /usr/share/java/jenkins-cli.jar

build:
	java -jar /usr/share/java/jenkins-cli.jar -s ${JENKINS_URI}  build 'VitexSoftware/multiflexi'

