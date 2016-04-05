package com.grappbox.grappbox.grappbox.Whiteboard;

import android.content.ContentValues;
import android.content.pm.PackageInstaller;
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
 * Created by tan_f on 23/03/2016.
 */
public class APIRequestOpenWhiteboard extends AsyncTask<String, Void, String> {

    private final static String _PATH = "whiteboard/pulldraw/";
    private Integer _APIResponse;
    private WhiteboardFragment _context;

    APIRequestOpenWhiteboard(WhiteboardFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result == null || _APIResponse != 200) {
            return ;
        }
/*
        List<ContentValues> whiteboardList = new Vector<ContentValues>();

        try {
            JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
            JSONArray whiteboardListArray = forecastJSON.getJSONArray("array");

            for (int i = 0; i < whiteboardListArray.length(); ++i)
            {
                JSONObject obj = whiteboardListArray.getJSONObject(i);
                ContentValues content = new ContentValues();

                content.put("id", obj.getString("id"));
                content.put("userId", obj.getString("userId"));
                content.put("name", obj.getString("name"));
                content.put("updatorId", obj.getString("updatorId"));
                content.put("createdAt", obj.getJSONObject("createdAt").getString("date"));
                if (!obj.getString("updatedAt").contains("null"))
                    content.put("updatedAt", obj.getJSONObject("updatedAt").getString("date"));
                if (!obj.getString("deletedAt").contains("null"))
                    content.put("deletedAt", obj.getString("deletedAt"));
                whiteboardList.add(content);
            }
            _context.fillList(whiteboardList);
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
        }*/
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;
        Log.v("param[0]", param[0]);
        Log.v("param[1]", param[1]);
        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + param[0]);
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            String token = SessionAdapter.getInstance().getToken();
            String lastUpdate = param[1];
            JSONParam.put("token", token);
            JSONParam.put("lastUpdate", lastUpdate);
            JSONData.put("data", JSONParam);
            APIConnectAdapter.getInstance().sendJSON(JSONData);

            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(_APIResponse));
            if (_APIResponse == 200 || _APIResponse == 206)
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            Log.v("JSON Content", resultAPI);
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
