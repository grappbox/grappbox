package com.grappbox.grappbox.grappbox.Whiteboard;

import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Iterator;

/**
 * Created by tan_f on 30/06/2016.
 */
public class APIRequestDeleteObjectWhiteboard extends AsyncTask<String, Void, String> {

    private final static String _PATH = "whiteboard/deleteobject";
    private Integer _APIResponse;
    private WhiteboardActivity _context;
    private ArrayList<DrawingShape> _list;
    private DrawingShape _tmpShape;

    APIRequestDeleteObjectWhiteboard(WhiteboardActivity context, ArrayList<DrawingShape> list, DrawingShape tmpShape){
        _context = context;
        _list = list;
        _tmpShape = tmpShape;
    }

    @Override
    protected void onPostExecute(String result) {
        if (result == null) {
            Log.v("Erase", "error");
            _list.add(_tmpShape);
        }
        _context.refresh();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String whiteboardId = param[0];
        String x = param[1];
        String y = param[2];
        String radius = param[3];
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH);
            APIConnectAdapter.getInstance().setRequestConnection("PUT");

            JSONObject JSONdata = new JSONObject();
            JSONObject JSONparam = new JSONObject();
            JSONObject JSONcenter = new JSONObject();

            JSONparam.put("token", SessionAdapter.getInstance().getToken().toString());
            JSONparam.put("whiteboardId", whiteboardId);
            JSONcenter.put("x", x);
            JSONcenter.put("y", y);
            JSONparam.put("center", JSONcenter);
            JSONparam.put("radius", radius);

            JSONdata.put("data", JSONparam);
            APIConnectAdapter.getInstance().sendJSON(JSONdata);
            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Delete Whiteboard", JSONdata.toString());
            if (_APIResponse == 200){
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("JSON Whiteboard", resultAPI);
            }

        } catch (IOException | JSONException e){
            e.printStackTrace();
            return null;
        }finally {
            APIConnectAdapter.getInstance().closeConnection();
        }

        return resultAPI;
    }

}
