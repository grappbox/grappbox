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
            float posStartX = Float.valueOf(param[2]);
            float posStartY = Float.valueOf(param[3]);
            float posEndX = Float.valueOf(param[4]);
            float posEndY = Float.valueOf(param[5]);
            String colorObject = param[6];
            String colorBackground = param[7];
            String text = param[8];
            String radiusArrahy = param[9];
            float radiusX = -1;
            float radiusY = -1;
            if (radiusArrahy != null) {
                Log.v("radius", radiusArrahy);
                String[] radius = radiusArrahy.split(";");
                radiusX = Float.valueOf(radius[0]);
                radiusY = Float.valueOf(radius[1]);
            }
            boolean isItalic = Boolean.getBoolean(param[10]);
            boolean isBold = Boolean.getBoolean(param[11]);
            float size = Float.valueOf(param[12]);
            String pointListX = param[13];
            String pointListY = param[14];
            float brushSize = Float.valueOf(param[15]);

            JSONParam.put("token", token);
            if (_type.equals("add")) {
                JSONObjParam.put("type", typeObject);
                JSONPositionStart.put("x", posStartX);
                JSONPositionStart.put("y", posStartY);
                JSONPositionEnd.put("x", posEndX);
                JSONPositionEnd.put("y", posEndY);
                JSONObjParam.put("positionStart", JSONPositionStart);
                JSONObjParam.put("positionEnd", JSONPositionEnd);
                JSONObjParam.put("lineWeight", brushSize);
                if (typeObject.equals("HANDWRITE")) {
                    Log.v("PointsX", pointListX);
                    Log.v("PointsY", pointListY);
                    List<String> pointX = new ArrayList<String>(Arrays.asList(pointListX.split(";")));
                    List<String> pointY = new ArrayList<String>(Arrays.asList(pointListY.split(";")));
                    JSONArray points = new JSONArray();
                    for (int i = 0; i < pointX.size(); ++i){
                        JSONObject coord = new JSONObject();
                        coord.put("x", Float.valueOf(pointX.get(i)));
                        coord.put("y", Float.valueOf(pointY.get(i)));
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
                if (radiusX != -1 && radiusY != -1) {
                    JSONObject radiusCoord = new JSONObject();
                    radiusCoord.put("x", radiusX);
                    radiusCoord.put("y", radiusY);
                    JSONObjParam.put("radius", radiusCoord);
                }
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
