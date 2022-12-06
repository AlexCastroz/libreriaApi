<?php

namespace App\Http\Controllers;

use App\Models\BookReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
class BookReviewController extends Controller
{

    public function addBookReview(Request $request){
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                //Set data
                $bookReview = new BookReview();
                $bookReview->comment = $request->comment;
                $bookReview->edited = false;
                $bookReview->book_id = $request->book["id"];
                $bookReview->user_id = auth()->user()->id;

                $bookReview->save();
                DB::commit();
                return $this->getResponse201('Book review', 'created', $bookReview);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }

    }

    public function updateBookReview(Request $request, $idComment){
        $userAuth = auth()->user();
        if (isset($userAuth->id)) {
            $book_review = BookReview::find($idComment);
            if($book_review){
                if($book_review->user_id == $userAuth->id){
                    $book_review->comment = $request->comment;
                    $book_review->edited = true;

                    $book_review->update();
                    return $this->getResponse201('review', 'updated', $book_review);
                }else{
                    return $this->getResponse403();
                }
            }
        }else{
            return $this->getResponse401();
        }
    }

}
