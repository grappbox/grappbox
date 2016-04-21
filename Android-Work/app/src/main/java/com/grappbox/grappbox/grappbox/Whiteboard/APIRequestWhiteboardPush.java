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
    //private DrawingShape _shape = null;

    APIRequestWhiteboardPush(DrawingView context, String typePush, DrawingShape shape)
    {
        _context = context;
        _type = typePush;
        //_shape = shape;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result == null || _APIResponse != 200) {
            Toast.makeText(_context.getContext(), "Error Happen", Toast.LENGTH_SHORT).show();
            return ;
        }
        /*if (_shape != null) {
            try {
                JSONObject obj = new JSONObject(result).getJSONObject("data");
                _shape.setId(obj.getInt("id"));
            } catch (JSONException e){
                e.printStackTrace();
            }
        }*/
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
            String colorBackgound = param[4];
            String text = param[5];
            String radius = param[6];
            boolean isItalic = Boolean.getBoolean(param[7]);
            boolean isBold = Boolean.getBoolean(param[8]);
            String size = param[9];
            JSONParam.put("token", token);
            JSONParam.put("modification", _type);
            if (_type.equals("add")) {
                JSONObjParam.put("type", typeObject);
                JSONObjParam.put("position", posObject);
                JSONObjParam.put("color", colorObject);
                if (!typeObject.equals("TEXT")) {
                    JSONObjParam.put("background", colorBackgound);
                } else {
                    JSONObjParam.put("text", text);
                    JSONObjParam.put("isItalic", isItalic);
                    JSONObjParam.put("isBold", isBold);
                    JSONObjParam.put("size", size);
                }
                if (radius != null)
                    JSONObjParam.put("radius", radius);
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
            }
        } catch (JSONException | IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
