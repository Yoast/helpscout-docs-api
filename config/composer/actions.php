<?php

namespace Yoast\HelpScout_Docs_API\Config\Composer;

use Composer\Script\Event;
use Exception;
use ReflectionException;
use RuntimeException;

/**
 * Class to handle Composer actions and events.
 */
class Actions {

	/**
	 * Provides a coding standards option choice.
	 *
	 * @param Event $event Composer event.
	 */
	public static function check_coding_standards( Event $event ) {
		$io = $event->getIO();

		$choices = [
			'1' => [
				'label'   => 'Check staged files for coding standard warnings & errors.',
				'command' => 'check-staged-cs',
			],
			'2' => [
				'label'   => 'Check current branch\'s changed files for coding standard warnings & errors.',
				'command' => 'check-branch-cs',
			],
			'3' => [
				'label'   => 'Check for all coding standard errors.',
				'command' => 'check-cs',
			],
			'4' => [
				'label'   => 'Check for all coding standard warnings & errors.',
				'command' => 'check-cs-warnings',
			],
			'5' => [
				'label'   => 'Fix auto-fixable coding standards.',
				'command' => 'fix-cs',
			],
		];

		$args = $event->getArguments();
		if ( empty( $args ) ) {
			foreach ( $choices as $choice => $data ) {
				$io->write( \sprintf( '%d. %s', $choice, $data['label'] ) );
			}

			$choice = $io->ask( 'What do you want to do? ' );
		}
		else {
			$choice = $args[0];
		}

		if ( isset( $choices[ $choice ] ) ) {
			$event_dispatcher = $event->getComposer()->getEventDispatcher();
			$event_dispatcher->dispatchScript( $choices[ $choice ]['command'] );
		}
		else {
			$io->write( 'Unknown choice.' );
		}
	}

	/**
	 * Runs lint on the staged files.
	 *
	 * Used the composer lint-files command.
	 *
	 * @param Event $event Composer event that triggered this script.
	 *
	 * @return void
	 */
	public static function lint_branch( Event $event ) {
		$branch = 'trunk';

		$args = $event->getArguments();
		if ( ! empty( $args ) ) {
			$branch = $args[0];
		}

		exit( self::lint_changed_files( $branch ) );
	}

	/**
	 * Runs lint on the staged files.
	 *
	 * Used the composer lint-files command.
	 *
	 * @return void
	 */
	public static function lint_staged() {
		exit( self::lint_changed_files( '--staged' ) );
	}

	/**
	 * Runs PHPCS on the staged files.
	 *
	 * Used the composer check-staged-cs command.
	 *
	 * @param Event $event Composer event that triggered this script.
	 *
	 * @return void
	 */
	public static function check_branch_cs( Event $event ) {
		$branch = 'trunk';

		$args = $event->getArguments();
		if ( ! empty( $args ) ) {
			$branch = $args[0];
		}

		exit( self::check_cs_for_changed_files( $branch ) );
	}

	/**
	 * Runs lint on changed files compared to some git reference.
	 *
	 * @param string $compare The git reference.
	 *
	 * @return int Exit code from the lint command.
	 */
	private static function lint_changed_files( $compare ) {
		\exec( 'git diff --name-only --diff-filter=d ' . \escapeshellarg( $compare ), $files );

		$php_files = self::filter_files( $files, '.php' );
		if ( empty( $php_files ) ) {
			echo 'No files to compare! Exiting.' . \PHP_EOL;

			return 0;
		}

		\system( 'composer lint-files -- ' . \implode( ' ', \array_map( 'escapeshellarg', $php_files ) ), $exit_code );

		return $exit_code;
	}

	/**
	 * Runs PHPCS on changed files compared to some git reference.
	 *
	 * @param string $compare The git reference.
	 *
	 * @return int Exit code passed from the coding standards check.
	 */
	private static function check_cs_for_changed_files( $compare ) {
		\exec( 'git diff --name-only --diff-filter=d ' . \escapeshellarg( $compare ), $files );

		$php_files = self::filter_files( $files, '.php' );
		if ( empty( $php_files ) ) {
			echo 'No files to compare! Exiting.' . \PHP_EOL;

			return 0;
		}

		\system( 'composer check-cs-warnings -- ' . \implode( ' ', \array_map( 'escapeshellarg', $php_files ) ), $exit_code );

		return $exit_code;
	}

	/**
	 * Filter files on extension.
	 *
	 * @param array  $files     List of files.
	 * @param string $extension Extension to filter on.
	 *
	 * @return array Filtered list of files.
	 */
	private static function filter_files( $files, $extension ) {
		return \array_filter(
			$files,
			function( $file ) use ( $extension ) {
				return \substr( $file, ( 0 - \strlen( $extension ) ) ) === $extension;
			}
		);
	}

	/**
	 * Color the output of the line.
	 *
	 * @param string $line  Line to output.
	 * @param string $color Color to give the line.
	 *
	 * @returns void
	 */
	private static function color_line( $line, $color ) {
		echo $color . $line . "\e[0m\n";
	}

	/**
	 * Color the line based on success status.
	 *
	 * @param string $line    Line to output.
	 * @param bool   $success Success status.
	 *
	 * @returns void
	 */
	private static function color_line_success( $line, $success ) {
		self::color_line( $line, ( $success ) ? "\e[32m" : "\e[31m" );
	}
}
