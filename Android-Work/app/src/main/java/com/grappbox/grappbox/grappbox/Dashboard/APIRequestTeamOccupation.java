package com.grappbox.grappbox.grappbox.Dashboard;

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

class APIRequestTeamOccupation extends AsyncTask<String, Void, String> {

    TeamOccupationFragment _context;

    APIRequestTeamOccupation(TeamOccupationFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result != null) {
            try {
                JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
                JSONArray arrayJSON = forecastJSON.getJSONArray("array");
                List<ContentValues> list = new Vector<ContentValues>();
                for (int i = 0; i < arrayJSON.length(); ++i) {
                    JSONObject obj = arrayJSON.getJSONObject(i);
                    ContentValues values = new ContentValues();
                    values.put("name", obj.getString("name"));
                    values.put("user_id", obj.getJSONObject("users").getString("id"));
                    values.put("first_name", obj.getJSONObject("users").getString("firstname"));
                    values.put("last_name", obj.getJSONObject("users").getString("lastname"));
                    values.put("occupation", obj.getString("occupation"));
                    values.put("number_of_tasks_begun", obj.getString("number_of_tasks_begun"));
                    values.put("number_of_ongoing_tasks", obj.getString("number_of_ongoing_tasks"));
                    list.add(values);
                }
                _context.createContentView(list);
            } catch (JSONException e){
                e.printStackTrace();
            }
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("dashboard/getteamoccupation/" + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            }

        } catch (IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}
