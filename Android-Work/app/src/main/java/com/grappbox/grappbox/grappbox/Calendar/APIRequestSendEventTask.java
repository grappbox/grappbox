package com.grappbox.grappbox.grappbox.Calendar;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;

import java.io.IOException;
import java.util.List;

/**
 * Created by tan_f on 21/01/2016.
 */
public class APIRequestSendEventTask extends AsyncTask<String, Void, List<ContentValues>> {

    AddEventActivity _context;

    APIRequestSendEventTask(AddEventActivity context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(List<ContentValues> result)
    {
        super.onPostExecute(result);
    }

     @Override
     protected List<ContentValues> doInBackground(String ... param)
     {
         String resultAPI;
         Integer APIResponse;
         List<ContentValues> listResult = null;

         try {
             APIConnectAdapter.getInstance().startConnection("event/postevent", "V0.2");
             APIConnectAdapter.getInstance().setRequestConnection("POST");

             APIResponse = APIConnectAdapter.getInstance().getResponseCode();
             Log.v("Response API :", APIResponse.toString());
             if (APIResponse == 200) {
/*                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                 listResult = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);*/
             } else {
                 return null;
             }

         } catch (IOException e){
             Log.e("APIConnection", "Error ", e);
             return null;
         } /*catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
         }*/finally {
             APIConnectAdapter.getInstance().closeConnection();
         }
         return listResult;
     }

}

