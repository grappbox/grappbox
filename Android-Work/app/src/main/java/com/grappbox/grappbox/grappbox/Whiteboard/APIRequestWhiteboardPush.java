package com.grappbox.grappbox.grappbox.Whiteboard;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.lang.reflect.Array;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

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
            _context.errorPush();
            return ;
        }
        Log.v("Push Whiteboard", result);
        try {
            JSONObject obj = new JSONObject(result).getJSONObject("data");
            _context.giveIdShapePush(obj.getInt("id"));
        } catch (JSONException e) {
            e.printStackTrace();
        }
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
            JSONObject JSONPositionStart = new JSONObject();
            JSONObject JSONPositionEnd = new JSONObject();

            String token = SessionAdapter.getInstance().getToken();
            String typeObject = param[1];
            String posStartX = param[2];
            String posStartY = param[3];
            String posEndX = param[4];
            String posEndY = param[5];
            String colorObject = param[6];
            String colorBackground = param[7];
            String text = param[8];
            String radius = param[9];
            boolean isItalic = Boolean.getBoolean(param[10]);
            boolean isBold = Boolean.getBoolean(param[11]);
            String size = param[12];
            String pointListX = param[13];
            String pointListY = param[14];

            JSONParam.put("token", token);
            JSONParam.put("modification", _type);
            if (_type.equals("add")) {
                JSONObjParam.put("type", typeObject);
                JSONPositionStart.put("x", posStartX);
                JSONPositionStart.put("y", posStartY);
                JSONPositionEnd.put("x", posEndX);
                JSONPositionEnd.put("y", posEndY);
                JSONObjParam.put("positionStart", JSONPositionStart);
                JSONObjParam.put("positionEnd", JSONPositionEnd);
                if (typeObject.equals("HANDWRITE")) {
                    Log.v("PointsX", pointListX);
                    Log.v("PointsY", pointListY);
                    List<String> pointX = new ArrayList<String>(Arrays.asList(pointListX.split(";")));
                    List<String> pointY = new ArrayList<String>(Arrays.asList(pointListY.split(";")));
                    JSONArray points = new JSONArray();
                    for (int i = 0; i < pointX.size(); ++i){
                        JSONObject coord = new JSONObject();
                        coord.put("x", pointX.get(i));
                        coord.put("y", pointY.get(i));
                        points.put(coord);
                    }
                    JSONObjParam.put("points", points);
                }
                JSONObjParam.put("color", colorObject);
                if (!typeObject.equals("TEXT")) {
                    JSONObjParam.put("background", colorBackground);
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
