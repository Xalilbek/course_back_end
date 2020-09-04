pipeline {
    agent any
    environment {
       ScriptPath = '/root/scripts/AutoDeploy.sh'
    }
    stages {
        stage('Set Jenkins job name') {
            steps { script { currentBuild.displayName = "Deploy_${BUILD_NUMBER}" } }
        }
        stage ('Start Deploying') {
            agent any 
            steps {
               script {
                    sshPublisher(
                      failOnError: false,
                      publishers: [sshPublisherDesc(configName: "E-Gundelik",transfers: [sshTransfer(execCommand: "${ScriptPath} ${JOB_NAME}")])])
               }  
            }
        }    
    }
    post { always { cleanWs() } }
}
