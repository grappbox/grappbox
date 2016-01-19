package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 19/01/2016.
 */
public class UploadFileTask {

    private CloudExplorerFragment   _context;
    private CloudFileAdapter        _adapter;
    private String                  _filename;
    private String                  _system_filepath;
    private int                     _streamId;
    private String                  _safePassword;

    UploadFileTask(CloudExplorerFragment context, CloudFileAdapter adapter, String system_filepath, String filename, String safePassword)
    {
        _context = context;
        _adapter = adapter;
        _filename = filename;
        _system_filepath = system_filepath;
        _safePassword = safePassword;
        _streamId = -1;
    }

    public void execute(String cloudPath, String filePassword)
    {
        OpenStreamTask task = new OpenStreamTask();

        task.execute(cloudPath, filePassword);
    }

    private boolean handleAPIError(JSONObject infos) throws JSONException {
        if (!infos.getString("return_code").startsWith("1."))
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + infos.getString("return_code"));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            if (_context != null) {
                _context.setSafePassword("");
            }
            return true;
        }
        return false;
    }

    private boolean disconnectAPI()
    {
        int responseCode = 500;

        try {
            responseCode = APIConnectAdapter.getInstance().getResponseCode();
        } catch (IOException e) {
            e.printStackTrace();
        }

        if (responseCode < 300) {
            APIConnectAdapter.getInstance().closeConnection();
        }
        else
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + String.valueOf(responseCode));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            if (_context != null) {
                _context.setSafePassword("");
            }
            return true;
        }
        return false;
    }

    private class OpenStreamTask extends AsyncTask<String, Void, String>
    {

        @Override
        protected String doInBackground(String... params) {
            APIConnectAdapter api = APIConnectAdapter.getInstance();
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            String token = SessionAdapter.getInstance().getToken();
            String safeURL;
            String cloudPath = params[0];
            String filePassword = params[1];
            int projectId = SessionAdapter.getInstance().getCurrentSelectedProject();

            safeURL = (_safePassword.equals("") ? "" : ("/" + _safePassword));
            try {
                data.put("path", cloudPath);
                if (!filePassword.equals(filePassword))
                    data.put("password", filePassword);
                data.put("filename", _filename);
                json.put("data", data);

                api.setVersion("V0.2");
                api.startConnection("cloud/stream/" + token + "/" + String.valueOf(projectId) + safeURL);
                api.setRequestConnection("POST");
                api.sendJSON(json);
                return api.getInputSream();
            } catch (JSONException e) {
                e.printStackTrace();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            PrepareChunkTask task = new PrepareChunkTask();
            if (disconnectAPI())
                return;

            try {
                JSONObject json = new JSONObject(s);
                JSONObject info = json.getJSONObject("info");
                JSONObject data = json.getJSONObject("data");

                assert info != null;
                if (handleAPIError(info))
                    return;
                assert data != null;
                _streamId = data.getInt("stream_id");
                task.execute(); // TODO : fill args to fit with prepare chunk arguments
            } catch (JSONException e) {
                e.printStackTrace();
            }

            super.onPostExecute(s);
        }
    }

    private class CloseStreamTask extends  AsyncTask<String, Void, String>
    {

        @Override
        protected String doInBackground(String... params) {
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
        }
    }

    private class PrepareChunkTask extends AsyncTask<String, Void, String>
    {
        //TODO : search about file intent in android
        @Override
        protected String doInBackground(String... params) {
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
        }
    }

    private class UploadChunkTask extends  AsyncTask<String, Void, String>
    {

        @Override
        protected String doInBackground(String... params) {
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
        }
    }
}
