#include "SFontLoader.h"
#include "CanvasTimeline.h"

CanvasTimeline::CanvasTimeline(int idTimeline, QWidget *parent) : QWidget(parent)
{
    _IDTimeline = idTimeline;
    _MainTimelineLayout = new QGridLayout();

    _MainTimelineLayout->setSpacing(0);
    _MainTimelineLayout->setContentsMargins(5, 5, 0, 0);

    _ContenerAddMessage = new QWidget();
    QWidget *mainW = new QWidget();
    _ContenerAddMessage->setLayout(new QVBoxLayout());
    _ContenerAddMessage->layout()->setContentsMargins(100, 0, 100, 30);
    _ContenerAddMessage->layout()->addWidget(mainW);
    mainW->setStyleSheet("background: #FFFFFF");
    _LayoutAddMessage = new QVBoxLayout();
    _LabelAddMessage = new QLabel("Add a new message");
    _LabelAddMessage->setStyleSheet("background: #2abb67; color: #FFFFFF; border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #FFFFFF; ");
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPixelSize(20);
    _LabelAddMessage->setAlignment(Qt::AlignCenter);
    _LabelAddMessage->setFont(font);

    _TitleLabel = new QLabel("Title");
    _TitleLabel->setStyleSheet("color: #424242; border-style:none; border-bottom-style:solid; border-width: 2px; border-color: #E0E0E0;");
    _TitleMessage = new QLineEdit();

    _MessageLabel = new QLabel("Message");
    _MessageLabel->setStyleSheet("color: #424242; border-style:none; border-bottom-style:solid; border-width: 2px; border-color: #E0E0E0;");
    _Message = new QTextEdit();
    _ConfirmAddingMessage = new QPushButton("Add");
    _ConfirmAddingMessage->setStyleSheet("QPushButton { "
                                         "background: #2abb67;"
                                         "color: #FFFFFF; "
                                         "border-style:none; "
                                         "border-bottom-style: solid; "
                                         "border-width: 1px; "
                                         "border-color: #FFFFFF;}"
                                         "QPushButton:hover {background: #1aab57;}"
                                         "QPushButton:pressed {background: #3acb77;}");
    _ConfirmAddingMessage->setFont(font);
    _LayoutAddMessage->addWidget(_LabelAddMessage);

    _LayoutAddMessage->addWidget(_TitleLabel);
    _LayoutAddMessage->addWidget(_TitleMessage);
    _LayoutAddMessage->addWidget(_MessageLabel);
    _LayoutAddMessage->addWidget(_Message);
    _LayoutAddMessage->addWidget(_ConfirmAddingMessage);

    _LayoutAddMessage->setContentsMargins(0, 0, 0, 0);
    _LayoutAddMessage->setSpacing(1);

    mainW->setLayout(_LayoutAddMessage);

    _MainTimelineLayout->addWidget(_ContenerAddMessage, 0, 0, 1, 3);

    for (int i = 1; i < 6; ++i)
    {
        ConversationTimeline *c = new ConversationTimeline(i, this);
        _Conversation.append(c);
        _MainTimelineLayout->addWidget(c, i, (i % 2) * 2, 1, 1);
        _MainTimelineLayout->addWidget(new LineTimeline(), i, 1, 1, 1);
        QObject::connect(c, SIGNAL(AnimOpenComment(int)), this, SLOT(UpdateTimelineAnim(int)));
    }

    setLayout(_MainTimelineLayout);

    QObject::connect(_ConfirmAddingMessage, SIGNAL(clicked(bool)), this, SLOT(AddingTimeline()));
}

void  CanvasTimeline::UpdateTimelineAnim(int Id)
{
    for (ConversationTimeline *item : _Conversation)
    {
        if (item->GetID() != Id)
            item->ForceCloseComment();
    }
}

void CanvasTimeline::AddingTimeline()
{

}
