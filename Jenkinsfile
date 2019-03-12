pipeline {
	agent any

	stages {
		stage('Run code analysis tests') {
			steps {
              script {
                docker.image('mysql:5.7').withRun('-e "MYSQL_USER=qsf" -e "MYSQL_PASSWORD=qsf" -e "MYSQL_DATABASE=qsf-test" -e "MYSQL_ROOT_PASSWORD=root"') { c_db ->
                    symfonyRunTests('test', ["${c_db.id}:qsf-db"], ["qsf-db:3306"], ["--no-scripts"])
                }
              }
			}
		}

		stage('install prod dependencies') {
			steps {
				symfonyComposerInstall('prod', true, ['--no-scripts'])
			}
		}

		stage('build prod') {
			steps {
				buildDockerImage('q1i/privacy-bundle', env.BRANCH_NAME)
			}
		}

		stage('install dev dependencies') {
			steps {
				symfonyComposerInstall('dev', false, ['--no-scripts'])
			}
		}

		stage('build dev') {
			steps {
				buildDockerImage('q1i/privacy-bundle-dev', env.BRANCH_NAME, ['-f Dockerfile.dev'])
			}
		}
	}
	post {
		always {
			cleanWs()
		}
	}
}