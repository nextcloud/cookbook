import test_runner.ci_printer
import test_runner.timer
import test_runner.ci_printer as l
import test_runner.proc as p

def pullImages(args, quiet=True):
	l.logger.printTask('Pulling pre-built images')
	cmd = ['docker-compose', 'pull']
	if quiet:
		cmd.append('--quiet')
	p.pr.run(cmd).check_returncode()

	imageName = 'nextcloudcookbook/testci:php{version}'.format(version=args.php_version[0])
	cmd = ['docker', 'pull', '--quiet', imageName]
	if not quiet:
		cmd = [x for x in cmd if x != '--quiet']
	sp = p.pr.run(cmd)
	if sp.returncode == 0:
		p.pr.run(['docker', 'tag', imageName, 'cookbook_unittesting_dut'])

	l.logger.printTask('Pulling images finished')

def buildImages(args, pull=True):
	l.logger.printTask('Building images')
	
	cmd = ['docker-compose', 'build', '--force-rm']
	if pull:
		cmd.append('--pull')
	if args.ci:
		cmd = cmd + ['--progress', 'plain']
	cmd = cmd + [
		'--build-arg', 'PHPVERSION={ver}'.format(ver=args.php_version[0]), 'dut', 'occ', 'php', 'fpm'
	]

	p.pr.run(cmd).check_returncode()

	p.pr.run(['docker-compose', 'build', '--pull', '--force-rm', 'mysql', 'postgres', 'www']).check_returncode()

	l.logger.printTask('Building images finished.')

def pushImages(args):
	l.logger.printTask('Retagging docker images')
	pushedName = 'nextcloudcookbook/testci:php{version}'.format(version=args.php_version[0])

	p.pr.run([
		'docker', 'tag', 'cookbook_unittesting_dut', pushedName
	]).check_returncode()

	p.pr.run([
		'docker', 'tag', 'cookbook_unittesting_mysql', 'nextcloudcookbook/mariadb-test:latest'
	]).check_returncode()

	p.pr.run([
		'docker', 'tag', 'cookbook_unittesting_postgres', 'nextcloudcookbook/postgres-test:latest'
	]).check_returncode()

	l.logger.printTask('Pushing docker images to repository')
	p.pr.run([
		'docker', 'push', pushedName
	]).check_returncode()

	p.pr.run([
		'docker', 'push', 'nextcloudcookbook/mariadb-test:latest'
	]).check_returncode()

	p.pr.run([
		'docker', 'push', 'nextcloudcookbook/postgres-test:latest'
	]).check_returncode()

def handleDockerImages(args):
	t = test_runner.timer.Timer()

	l.logger.startGroup('Prepare docker')
	t.tic()

	if args.pull or args.create_images:
		pullImages(args, not args.verbose)
		t.toc('Pulling done')
	
	if args.create_images:
		pull = not args.pull_php_base_image
		buildImages(args, pull)
		t.toc('Building done')
	
	if args.push_images:
		pushImages(args)
	
	l.logger.endGroup()
	t.toc()
