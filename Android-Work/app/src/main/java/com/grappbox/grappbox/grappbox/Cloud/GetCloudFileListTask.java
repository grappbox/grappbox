package com.grappbox.grappbox.grappbox.Cloud;

import android.app.Activity;
import android.content.Context;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.ProtocolException;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class GetCloudFileListTask extends AsyncTask<String, Void, String> {

    private CloudFileAdapter _adapter;
    private String          _askedPath;

    GetCloudFileListTask(CloudFileAdapter adapter)
    {
        _adapter = adapter;
    }
    @Override
    protected String doInBackground(String... params) {
        String token = SessionAdapter.getInstance().getToken();
        int projectId = SessionAdapter.getInstance().getCurrentSelectedProject();
        String path = params[0];
        String passwordSafe = params[1];
        APIConnectAdapter api = APIConnectAdapter.getInstance();

        Log.e("API", path);
        _askedPath = path;
        path = path.replace('/', ',');
        try {
            api.startConnection("cloud/getlist/" + token + "/" + String.valueOf(projectId) + "/" + path + (passwordSafe == "" ? "" : "/" + passwordSafe));
            api.setRequestConnection("GET");

            return api.getInputSream();
        } catch (ProtocolException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        APIConnectAdapter.getInstance().closeConnection();
        JSONObject json = null;
        JSONArray data;

        _adapter.clear();
        if (!_askedPath.equals("/"))
        {
            FileItem item = new FileItem(FileItem.EFileType.BACK, "Go to parent");

            _adapter.add(item);
        }
        try {
            json = new JSONObject(s);
            data = json.getJSONArray("data");
            for (int i = 0; i < data.length(); ++i)
            {
                FileItem file = new FileItem();

                file.fromJson(data.getJSONObject(i));

                _adapter.add(file);
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
        super.onPostExecute(s);
    }
}
