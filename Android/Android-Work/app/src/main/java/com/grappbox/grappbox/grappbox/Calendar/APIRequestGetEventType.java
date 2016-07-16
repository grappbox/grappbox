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

    private final static String PATH = "event/gettypes/";
    private EventActivity _context;

    APIRequestGetEventType(EventActivity context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null) {
            try {

                List<ContentValues> eventTypes = new Vector<ContentValues>();
                JSONObject obj = new JSONObject(result).getJSONObject("data");
                JSONArray array = obj.getJSONArray("array");

                for (int i = 0; i < array.length(); ++i){
                    JSONObject project = array.getJSONObject(i);
                    ContentValues eventData = new ContentValues();

                    eventData.put("id", project.getString("id"));
                    eventData.put("name", project.getString("name"));
                    eventTypes.add(eventData);
                }
                _context.fillEventListSpinner(eventTypes);

                Log.v("GetEventType :", result);
            } catch (JSONException e){
                e.printStackTrace();
            }
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection(PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
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
