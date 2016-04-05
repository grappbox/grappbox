package com.grappbox.grappbox.grappbox.Whiteboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 27/03/2016.
 */
public class APIRequestAddWhiteboard extends AsyncTask<String, Void, String> {

    private final static String _PATH = "whiteboard/new";
    private Integer _APIResponse;
    private WhiteboardListFragment _context;

    APIRequestAddWhiteboard(WhiteboardListFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        Log.v("Add Whiteboard result", result);
        if (_APIResponse >= 200 && _APIResponse < 300)
        {
            APIRequestGetWhiteboardList api = new APIRequestGetWhiteboardList(_context);
            api.execute();
            return;
        }
        Toast.makeText(_context.getContext(), "An error occured", Toast.LENGTH_SHORT).show();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH);
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();

            Log.v("projectId", param[0]);
            Log.v("WhiteboardName", param[1]);

            String token = SessionAdapter.getInstance().getToken();
            String projectId = param[0];
            String whiteboardName = param[1];

            JSONParam.put("token", token);
            JSONParam.put("projectId", projectId);
            JSONParam.put("whiteboardName", whiteboardName);

            JSONData.put("data", JSONParam);

            APIConnectAdapter.getInstance().sendJSON(JSONData);
            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(_APIResponse));
            if (_APIResponse >= 200 && _APIResponse < 300)
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            Log.v("JSON Content", resultAPI);
        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
