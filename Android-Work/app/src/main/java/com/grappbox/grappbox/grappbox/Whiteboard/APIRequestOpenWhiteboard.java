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

        List<ContentValues> whiteboardForm = new Vector<ContentValues>();
        try {
            JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
            JSONArray addForm = forecastJSON.getJSONArray("add");
            JSONArray deleteForm = forecastJSON.getJSONArray("delete");
            JSONObject obj;

            Log.v("JSON add", addForm.toString());
            for (int i = 0; i < addForm.length(); ++i)
            {
                obj = addForm.getJSONObject(i);
                ContentValues content = new ContentValues();

                content.put("form", "add");
                content.put("id", obj.getString("id"));
                content.put("whiteboardId", obj.getString("whiteboardId"));
                String object = obj.getString("object");
                JSONObject objectData = new JSONObject(object);
                content.put("type", objectData.getString("type"));
                content.put("color", objectData.getString("color"));
                if (objectData.has("background"))
                    content.put("background", objectData.getString("background"));
                else
                    content.put("background", objectData.getString("color"));
                if (objectData.has("points"))
                    content.put("points", objectData.getString("points"));
                content.put("positionStartX", objectData.getJSONObject("positionStart").getString("x"));
                content.put("positionStartY", objectData.getJSONObject("positionStart").getString("y"));
                content.put("positionEndX", objectData.getJSONObject("positionEnd").getString("x"));
                content.put("positionEndY", objectData.getJSONObject("positionEnd").getString("y"));
                whiteboardForm.add(content);
            }
            for (int i = 0; i < deleteForm.length(); ++i)
            {
                obj = deleteForm.getJSONObject(i);
                ContentValues content = new ContentValues();

                content.put("form", "delete");
                content.put("id", obj.getString("id"));
                content.put("whiteboardId", obj.getString("whiteboardId"));
                whiteboardForm.add(content);
            }
            _context.refreshWhiteboard(whiteboardForm);

        } catch (JSONException j){
            j.printStackTrace();
        }
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
            Log.v("JSON Whiteboard", resultAPI);
        } catch (JSONException | IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
