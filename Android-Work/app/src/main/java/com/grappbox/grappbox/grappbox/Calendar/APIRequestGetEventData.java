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
public class APIRequestGetEventData  extends AsyncTask<String, Void, String> {

    private EventDetailFragment _context;
    private int _idEvent;

    APIRequestGetEventData(EventDetailFragment context, int idEvent)
    {
        _context = context;
        _idEvent = idEvent;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        ContentValues eventData = new ContentValues();
        List<ContentValues> eventListUser = new Vector<ContentValues>();

        if (result == null)
            return ;
        try
        {
            JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
            JSONArray userList = forecastJSON.getJSONArray("users");

            eventData.put("id", forecastJSON.getString("id"));
            eventData.put("projectId", forecastJSON.getString("projectId"));
            eventData.put("type", forecastJSON.getJSONObject("type").getString("name"));
            eventData.put("title", forecastJSON.getString("title"));
            eventData.put("icon", forecastJSON.getString("icon"));
            eventData.put("description", forecastJSON.getString("description"));
            eventData.put("beginDate", forecastJSON.getJSONObject("beginDate").getString("date"));
            eventData.put("endDate", forecastJSON.getJSONObject("endDate").getString("date"));

            for (int i = 0; i < userList.length(); ++i)
            {
                JSONObject userJSON = userList.getJSONObject(i);
                ContentValues user = new ContentValues();
                user.put("id", userJSON.getString("id"));
                user.put("name", userJSON.getString("name"));
                user.put("email", userJSON.getString("email"));
                user.put("avatar", userJSON.getString("avatar"));
                eventListUser.add(user);
            }
            _context.fillContentData(eventData, eventListUser);
            APIrequestGetUserProject project = new APIrequestGetUserProject(_context);
            project.execute();
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            String token = SessionAdapter.getInstance().getToken();
            APIConnectAdapter.getInstance().startConnection("event/getevent/" + token + "/" + String.valueOf(_idEvent), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("Result API :", resultAPI);
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
