package com.grappbox.grappbox.grappbox.Whiteboard;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 25/03/2016.
 */
public class APIRequestWhiteboardPush extends AsyncTask<String, Void, String> {


    private final static String _PATH = "whiteboard/pushdraw/";
    private Integer _APIResponse;
    private DrawingView _context;
    private String    _type;

    APIRequestWhiteboardPush(DrawingView context, String typePush)
    {
        _context = context;
        _type = typePush;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result == null || _APIResponse != 200) {
            Toast.makeText(_context.getContext(), "Error Happen", Toast.LENGTH_SHORT).show();
            return ;
        }
        Log.v("Push Whiteboard", result);
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + param[0]);
            APIConnectAdapter.getInstance().setRequestConnection("PUT");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            JSONObject JSONObjParam = new JSONObject();
            String token = SessionAdapter.getInstance().getToken();
            String typeObject = param[1];
            String posObject = param[2];
            String colorObject = param[3];
            JSONParam.put("token", token);
            JSONParam.put("modification", _type);
            if (_type.equals("add")) {
                JSONObjParam.put("type", typeObject);
                JSONObjParam.put("position", posObject);
                JSONObjParam.put("color", colorObject);
                JSONParam.put("object", JSONObjParam);
            } else if (_type.equals("del")) {
                JSONParam.put("objectId",  Integer.getInteger(param[1]));
            }
            JSONData.put("data", JSONParam);
            Log.v("Obj Push", JSONParam.toString());
            APIConnectAdapter.getInstance().sendJSON(JSONData);
            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(_APIResponse));
            if (_APIResponse == 200 || _APIResponse == 206) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("JSON Content", resultAPI);
            }
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
