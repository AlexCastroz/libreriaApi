<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // public BookController(){

    // }
    public function index(){
        $books = Book::with('authors', "category", "editorial")->get();
        return [
            "error" => false,
            "message" => "Successfull query",
            "data" => $books
        ];
    }

    public function store(Request $request){
        DB::beginTransaction();
        try{
            $existIsbn = Book::where('isbn', trim($request->isbn))->exists();
            if(!$existIsbn){
                $book = new Book();
                $book->isbn = trim($request->isbn);
                $book->title = $request->title;
                $book->description = $request->description;
                $book->category_id = $request->category["id"];
                $book->editorial_id = $request->editorial["id"];
                $book->publish_date = Carbon::now();
                $book->save();
                foreach($request->authors as $item){
                    $book->authors()->attach($item);
                }
                $bookId = $book->id;
                return [
                    "status" => true,
                    "message" => "Your book has been created",
                    "data" => [
                        "book_id" => $bookId,
                        "book" => $book
                    ]
                ];
            }else{
                return [
                    "status" => false,
                    "message" => "The book already exist",
                    "data" => []
                ];
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
        }

    }

    public function update(Request $request, $id){
        try{
            $response = $this->getResponse();
            $book = Book::find($id);
            if($book){
                $isbnOwner = Book::where("isbn", $request->isbn)->first();
                if(!$isbnOwner || $isbnOwner->id == $book->id){
                    $book->isbn = trim($request->isbn);
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->category_id = $request->category["id"];
                    $book->editorial_id = $request->editorial["id"];
                    $book->publish_date = Carbon::now();

                    //Delete
                    foreach($book->authors as $item){
                        $book->authors()->detach($item->id);
                    }
                    $book->update();

                    //Add
                    foreach($request->authors as $item){
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')->where("id", $id)->get();
                    $response["error"] = false;
                    $response["data"] = $book;
                    $response["message"] = "Your Book has been updated";
                }else{
                    $response["message"] = "ISBN duplicated";
                }
                return $response;
            }else{
                $response["message"] = "404 not found";
                return $response;
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
        }
    }

    public function getById($id){
        DB::beginTransaction();
        try{
            $book = Book::with('authors', "category", "editorial")->where("id", $id)->get();
            if($book){
                $response["error"] = false;
                $response["message"] = "Successfull query!";
                $response["data"] = $book;
            }else{
                $response["message"] = "Not found!";
            }
            return $response;
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
        }

    }

    public function deleteById($id){
        DB::beginTransaction();
        try{
            $response = $this->getResponse();
            $book = Book::find($id);
            if($book){
                foreach ($book->authors as $item) {
                    $book->authors()->detach($item->id);
                }
                $book->delete();
                $response["error"] = false;
                $response["message"] = "Your book has been deleted!";
                $response["data"] = $book;
            }else{
                $response["message"] = "Not found!";
            }
            return $response;
        }catch(Exception $e){
            DB::rollBack();
        }
    }
}
