using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI;
using Windows.UI.Xaml.Media;

namespace Grappbox
{
    public static partial class Constants
    {
        public const string DashboardGenericCall = "dashboard/projects";
        public const string DashboardTeamOccupationCall = "dashboard/occupation";
        public const string DashboardMeetingsCall = "dashboard/meetings";
        public const string CalendarDayCall = "planning/day";
        public const string CalendarMonthCall = "planning/month";
        public const string GetProjectUsers = "project/users";
        public const string PostEvent = "event";
        public const string GetProjectTasks = "tasks/project";
        public const string GetProjectTags = "tasks/tags/project";
        public const string CreateWhiteboard = "whiteboard";
        public const string CloseWhiteboard = "whiteboard/";
        public const string DeleteWhiteboard = "whiteboard/";
        public const string DeleteWhiteboardObject = "whiteboard/object/";
        public const string ListWhiteboards = "whiteboards";
        public const string OpenWhiteboards = "whiteboard";
        public const string PullWhiteboard = "whiteboard/draw/";
        public const string PushWhiteboard = "whiteboard/draw/";
    }
}