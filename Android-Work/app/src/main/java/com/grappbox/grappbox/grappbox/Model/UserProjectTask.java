package com.grappbox.grappbox.grappbox.Model;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;

/**
 * Created by wieser_m on 24/01/2016.
 */
public class UserProjectTask extends AsyncTask<String, Void, String> {
    private Activity _context;
    private APIConnectAdapter _api;

    public UserProjectTask(Activity context)
    {
        _context = context;
    }

    private boolean handleAPIError(JSONObject infos) throws JSONException {
        if (!infos.getString("return_code").startsWith("1."))
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_context);

            builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            if (_context instanceof MainActivity)
            {
                ((MainActivity) _context).logoutUser();
            }
            return true;
        }
        return false;
    }

    private boolean disconnectAPI() throws IOException {
        int responseCode = 500;

        responseCode = _api.getResponseCode();
        if (responseCode < 300) {
            APIConnectAdapter.getInstance().closeConnection();
        }
        else
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_context);

            builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            if (_context instanceof MainActivity)
                ((MainActivity) _context).logoutUser();
            return true;
        }
        return false;
    }

    @Override
    protected String doInBackground(String... params) {
        _api = APIConnectAdapter.getInstance(true);

        try {
            _api.setVersion("V0.2");
            _api.startConnection("user/getprojects/" + SessionAdapter.getInstance().getToken());
            _api.setRequestConnection("GET");
            return _api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        ArrayList<ProjectModel> receivedData = new ArrayList<>();
        JSONObject json, info, data;
        JSONArray array;

        if (s == null)
            return;
        assert s != null;
        try {
            json = new JSONObject(s);
            info = json.getJSONObject("info");
            data = json.getJSONObject("data");
            if (disconnectAPI())
                return;
            assert info != null;
            if (handleAPIError(info))
                return;
            assert data != null;
            array = data.getJSONArray("array");
            assert array != null;
            for (int i = 0; i < array.length(); ++i) {
                JSONObject obj = array.getJSONObject(i);
                receivedData.add(new ProjectModel(obj));
            }
        } catch (JSONException | IOException e) {
            e.printStackTrace();
        }
        if (_context instanceof MainActivity)
            ((MainActivity) _context).setProjectList(receivedData);
        super.onPostExecute(s);
    }
}
