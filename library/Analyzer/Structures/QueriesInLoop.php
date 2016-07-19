<?php
/*
 * Copyright 2012-2016 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Analyzer\Structures;

use Analyzer;

class QueriesInLoop extends Analyzer\Analyzer {
    public function analyze() {
        // for() { mysql_query(); }
        $this->atomIs(array('Foreach', 'For', 'While'))
             ->outIs('BLOCK')
             ->atomInside('Functioncall')
             ->codeIs(array('mssql_query',
                            'mysqli_query',
                            'mysqli_unbuffered_query',
                            'mysqli_db_query',
                            
                            'mysql_query',
                            'mysql_unbuffered_query',
                            'mysql_db_query',
                            
                            'pg_query',
                            
                            'sqlite_array_query',
                            'sqlite_single_query',
                            'sqlite_unbuffered_query',
                            ))
             ->back('first');
        $this->prepareQuery();

        // for() { $pdo->query(); }
        $this->atomIs(array('Foreach', 'For', 'While'))
             ->outIs('BLOCK')
             ->atomInside('Functioncall')
             ->hasIn('METHOD')
             ->codeIs('query') // PDO, cyrus
             ->back('first');
        $this->prepareQuery();

        // for() { somefunction(query()); }

    }
}

?>
