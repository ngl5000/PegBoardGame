<?php

class Solve_peg_board {

  	var $rows                       = 4;
	var $peg_holes                  = 0;
	var $num_pegs                   = 0;
	var $winner_hole                = 8;
	var $board                      = array();
	var $moves_trail                = array();


	/**
	 * Session Constructor
	 *
	 * 
	 */
	public function __construct($num_rows=5)
	{
		if($num_rows < 4)
		{
			exit(1);
		}
		
		$this->rows = $num_rows;
		
		$this->set_num_peg_holes();
		
		$this->build_board();
	}
	
	// --------------------------------------------------------------------
	
	public function get_solution()
	{
		return $this->recurs_board($this->board);
	}
	
	// --------------------------------------------------------------------
	
	public function show_first_moves()
	{
		return $this->find_moves($this->board);
	}
	
	// --------------------------------------------------------------------
	
	public function show_board()
	{
		return $this->board;
	}
	
	// --------------------------------------------------------------------
	
	public function number_of_peg_holes()
	{
		return $this->peg_holes;
	}
	
	// --------------------------------------------------------------------

	private function find_hole_num($row_num, $horizontal_placement)
	{
		return $this->recurs_addition($row_num-1) + $horizontal_placement;
	}

	// --------------------------------------------------------------------

	private function find_coordinate($hole_num, $column = FALSE)
	{
		$row_num = 1;
		$peg_count = 1;
		
		while($this->peg_holes - $peg_count >= 0)
		{
			if( ($hole_num - $peg_count) <= 0 )
			{
				if($column == TRUE)
				{
					return $hole_num - $peg_count + $row_num;
				}
				else
				{
					return $row_num;	
				}
			}
			
			$row_num++;
			
			$peg_count = $peg_count + $row_num;
		}
	}

	// --------------------------------------------------------------------

	// Used to get the actual peg position from the coordinates
	private function recurs_addition($row_num)
	{
		if($row_num < 2)
		{
			return $row_num;
		}
		else
		{
			return $row_num + $this->recurs_addition($row_num-1);
		}
	}
	
	// --------------------------------------------------------------------
	private function set_num_peg_holes()
	{
		for($i = $this->rows; $i > 0; $i--)
		{
			$this->peg_holes += $i;
		}
		
		$this->num_pegs = $this->peg_holes - 1;
	}

	// --------------------------------------------------------------------

	// Build a dynamic board (multi dimensional array)
	private function build_board()
	{
		for($i = $this->rows; $i > 0; $i--)
		{
			for($j = $i; $j > 0; $j--)
			{				
				$is_peg = TRUE;
				
				// Put no peg in flag hole
				if( $i == $this->rows )
				{
					if( round($i/2) == $j )
					{
						$is_peg = FALSE;
						
						// Let's ID the winning hole
						$this->winner_hole = $this->find_hole_num($i, $j);
					}
				}
				
				$this->board[$i][$j] = $is_peg;
			}
		}
	}
	
	// --------------------------------------------------------------------

	// Recursively go through peg board to find answer
	private function recurs_board($board)
	{
		// If this is a winning board we can stop here
		if( is_null($moves = $this->find_moves($board)) )
		{
			if($this->check_for_winning_board($board) === TRUE)
			{
				return $this->moves_trail;
			}
			else
			{
				// If this was not a winner and there are no moves left this is a dead end!!
				// Start from scratch
				$this->moves_trail = array();
			}
		}
		else
		{			
			// Recurse for each of the new moves 
			foreach($moves as $move)
			{
				$this->recurs_board($this->do_move($board, $move));
			}
		}
	}
	
	// --------------------------------------------------------------------
		
	private function do_move($board, $move)
	{
		// Document the moves during this instance
		$this->moves_trail[] = $move;
		
		$board[$this->find_coordinate($move['move_peg'])][$this->find_coordinate($move['move_peg'],TRUE)] = FALSE;
		$board[$this->find_coordinate($move['jump_peg'])][$this->find_coordinate($move['jump_peg'],TRUE)] = FALSE;
		$board[$this->find_coordinate($move['open_spot'])][$this->find_coordinate($move['open_spot'],TRUE)] = TRUE;
		
		return $board;
	}
	// --------------------------------------------------------------------

	// Whelp, that didn't work, lets find some more possibilities
	private function find_moves($board)
	{
		$available_moves = NULL;
		
		foreach($board as $row_num => $row)
		{
			foreach($row as $horizontal_placement => $is_peg)
			{
				// First find spots with no pegs
				if($is_peg === FALSE)
				{
					// Then check if there are surrounding pegs
					//HORIZONTALS
					if( isset($board[$row_num][$horizontal_placement-1]) && $board[$row_num][$horizontal_placement-1] === TRUE && isset($board[$row_num][$horizontal_placement-2]) && $board[$row_num][$horizontal_placement-2] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num, $horizontal_placement-1),
									'move_peg' => $this->find_hole_num($row_num, $horizontal_placement-2),
									);
					}
					if( isset($board[$row_num][$horizontal_placement+1]) && $board[$row_num][$horizontal_placement+1] === TRUE && isset($board[$row_num][$horizontal_placement+2]) && $board[$row_num][$horizontal_placement+2] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num, $horizontal_placement+1),
									'move_peg' => $this->find_hole_num($row_num, $horizontal_placement+2),
									);
					}
					//Same check but verticals UP
					if( isset($board[$row_num-1][$horizontal_placement-1]) && $board[$row_num-1][$horizontal_placement-1] === TRUE && isset($board[$row_num-2][$horizontal_placement-2]) && $board[$row_num-2][$horizontal_placement-2] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num-1, $horizontal_placement-1),
									'move_peg' => $this->find_hole_num($row_num-2, $horizontal_placement-2),
									);
					}
					if( isset($board[$row_num-1][$horizontal_placement]) && $board[$row_num-1][$horizontal_placement] === TRUE && isset($board[$row_num-2][$horizontal_placement]) && $board[$row_num-2][$horizontal_placement] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num-1, $horizontal_placement),
									'move_peg' => $this->find_hole_num($row_num-2, $horizontal_placement),
									);
					}
					//Same check but verticals DOWN
					if( isset($board[$row_num+1][$horizontal_placement+1]) && $board[$row_num+1][$horizontal_placement+1] === TRUE && isset($board[$row_num+2][$horizontal_placement+2]) && $board[$row_num+2][$horizontal_placement+2] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num+1, $horizontal_placement+1),
									'move_peg' => $this->find_hole_num($row_num+2, $horizontal_placement+2),
									);
					}
					if( isset($board[$row_num+1][$horizontal_placement]) && $board[$row_num+1][$horizontal_placement] === TRUE && isset($board[$row_num+2][$horizontal_placement]) && $board[$row_num+2][$horizontal_placement] === TRUE)
					{
						$available_moves[] = array(
									'open_spot' => $this->find_hole_num($row_num, $horizontal_placement),
									'jump_peg' => $this->find_hole_num($row_num+1, $horizontal_placement),
									'move_peg' => $this->find_hole_num($row_num+2, $horizontal_placement),
									);
					}
				}
			}
		}
		
		return $available_moves;
	}
	
	// --------------------------------------------------------------------

	// Let's see if we have ourselves a winner??
	private function check_for_winning_board($passed_board)
	{
		// Go through each peg hole and make sure the pattern matches
		foreach($passed_board as $row_num => $row)
		{
			foreach($row as $horizontal_placement => $is_peg)
			{
				// If not winner return false
				if($this->find_hole_num($row_num, $horizontal_placement) === $this->winner_hole AND $is_peg == FALSE)
				{
					return FALSE;
				}
				else if($is_peg === TRUE)
				{
					return FALSE;
				}
			}
		}
		
		// WOOOOOOOO!
		return TRUE;
	}
	
}
// END Session Class
