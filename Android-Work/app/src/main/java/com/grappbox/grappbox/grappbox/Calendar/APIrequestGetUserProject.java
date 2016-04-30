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
 * Created by tan_f on 05/02/2016.
 */
public class APIrequestGetUserProject extends AsyncTask<String, Void, String> {

    private EventFragment _context;

    APIrequestGetUserProject(EventFragment context)
    {
        _context = context;
    }

    private void manageError()
    {

    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);

        if (result != null) {


            try {

                List<ContentValues> projectUser = new Vector<ContentValues>();

                JSONObject obj = new JSONObject(result).getJSONObject("data");
                JSONArray array = obj.getJSONArray("array");

                for (int i = 0; i < array.length(); ++i) {
                    JSONObject project = array.getJSONObject(i);
                    ContentValues eventData = new ContentValues();

                    eventData.put("id", project.getString("id"));
                    eventData.put("name", project.getString("name"));
                    projectUser.add(eventData);
                }
                _context.fillProjectListSpinner(projectUser);

            } catch (JSONException j) {
                Log.e("APIConnection", "Error ", j);
            }

        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("user/getprojects/" + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN), "V0.2");
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
