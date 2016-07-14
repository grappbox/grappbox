package com.grappbox.grappbox.grappbox.Calendar;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 27/01/2016.
 */
public class APIRequestGetEventType extends AsyncTask<String, Void, String> {

    private EventDetailActivity _context;

    APIRequestGetEventType(EventDetailActivity context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null)
            Log.v("GetEventType :", result);
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("event/getTypes/" + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");


            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            } else {
                return null;
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
