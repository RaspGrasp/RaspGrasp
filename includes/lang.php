<?php

declare(strict_types=1);

const SUPPORTED_LANGS = ['en', 'ar', 'ja'];

function resolve_lang(): string
{
    $requested = $_GET['lang'] ?? null;
    if ($requested !== null && in_array($requested, SUPPORTED_LANGS, true)) {
        $_SESSION['lang'] = $requested;
        setcookie('lang', $requested, time() + 60 * 60 * 24 * 365, '/');
    }
    $lang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');
    return in_array($lang, SUPPORTED_LANGS, true) ? $lang : 'en';
}

function next_lang(string $current): string
{
    $i = array_search($current, SUPPORTED_LANGS, true);
    return SUPPORTED_LANGS[($i + 1) % count(SUPPORTED_LANGS)];
}

function is_rtl(string $lang): bool
{
    return $lang === 'ar';
}

function site_display_name(string $lang): string
{
    return ['en' => 'RaspGrasp', 'ar' => 'قهوة عطية', 'ja' => 'たまり場'][$lang] ?? 'RaspGrasp';
}

function category_label(string $lang, string $slug, string $fallback): string
{
    $map = [
        'ar' => ['general' => 'عام', 'tech' => 'تكنولوجيا', 'politics' => 'سياسة', 'anime' => 'أنمي'],
        'ja' => ['general' => '一般', 'tech' => 'テック', 'politics' => '政治', 'anime' => 'アニメ'],
    ];
    return $map[$lang][$slug] ?? $fallback;
}

function t(string $key): string
{
    global $lang;
    static $strings = [
        'tagline' => ['en' => 'the public noticeboard', 'ar' => 'لوحة الإعلانات العامة', 'ja' => '公共の掲示板'],
        'sections' => ['en' => 'Sections', 'ar' => 'الأقسام', 'ja' => 'セクション'],
        'account' => ['en' => 'Account', 'ar' => 'الحساب', 'ja' => 'アカウント'],
        'your_profile' => ['en' => 'Your profile', 'ar' => 'ملفك الشخصي', 'ja' => 'マイプロフィール'],
        'start_topic' => ['en' => 'Start a topic', 'ar' => 'ابدأ موضوع', 'ja' => 'トピックを作成'],
        'log_out' => ['en' => 'Log out', 'ar' => 'تسجيل الخروج', 'ja' => 'ログアウト'],
        'log_in' => ['en' => 'Log in', 'ar' => 'تسجيل الدخول', 'ja' => 'ログイン'],
        'create_account' => ['en' => 'Create account', 'ar' => 'إنشاء حساب', 'ja' => 'アカウント作成'],
       'notice_text' => ['en' => 'Say what you mean. Read what others mean.', 'ar' => 'قول رأيك من غير ما تبقى عمك في الفرح.', 'ja' => '本音で話そう。建前ばっかりだと、掲示板が退屈になるから。'],
        'made_for_web' => ['en' => 'made for the open web', 'ar' => 'اتصنع للويب المفتوح', 'ja' => 'オープンウェブのために'],
        'signed_in_as' => ['en' => 'signed in as', 'ar' => 'مسجّل دخول باسم', 'ja' => 'ログイン中：'],
        'latest_topics' => ['en' => 'Latest topics', 'ar' => 'أحدث المواضيع', 'ja' => '最新トピック'],
        'home' => ['en' => 'home', 'ar' => 'الرئيسية', 'ja' => 'ホーム'],
        'community_index' => ['en' => 'community index', 'ar' => 'فهرس المجتمع', 'ja' => 'コミュニティ一覧'],
        'hero_headline' => ['en' => 'Ideas, arguments, and the occasional excellent link.', 'ar' => 'أفكار، نقاشات، وأحيانًا لينك يفيد فعلاً.', 'ja' => 'アイデア、議論、そしてたまに良いリンク。'],
        'hero_sub' => ['en' => 'RaspGrasp is a small public forum for conversations that do not fit inside a 280-character box.', 'ar' => 'ده منتدى صغير عشان الكلام اللي مش بيتظبط في 280 حرف.', 'ja' => 'ここは280文字に収まらない会話のための小さな公開フォーラムです。'],
        'filtered_by' => ['en' => 'filtered by', 'ar' => 'مفلتر حسب', 'ja' => '絞り込み：'],
        'show_all' => ['en' => 'show all', 'ar' => 'عرض الكل', 'ja' => 'すべて表示'],
        'new_topic' => ['en' => '+ New topic', 'ar' => '+ موضوع جديد', 'ja' => '+ 新規トピック'],
        'join_discussion' => ['en' => 'Join the discussion', 'ar' => 'شارك في النقاش', 'ja' => '議論に参加'],
        'empty_title' => ['en' => 'No topics here yet.', 'ar' => 'مفيش مواضيع لسه.', 'ja' => 'まだトピックがありません'],
        'empty_sub' => ['en' => 'This is a clean page. Someone should probably ruin it with an opinion.', 'ar' => 'الصفحة دي فاضية. حد لازم يبوظها برأي.', 'ja' => 'まっさらなページです。誰かが意見で汚すべきでしょう。'],
        'start_first' => ['en' => 'Start the first topic', 'ar' => 'ابدأ أول موضوع', 'ja' => '最初のトピックを作成'],
        'terms_of_use' => ['en' => 'Terms of Use', 'ar' => 'شروط الاستخدام', 'ja' => '利用規約'],
        'on_github' => ['en' => 'on GitHub', 'ar' => 'على GitHub', 'ja' => 'GitHubで見る'],
        'replies' => ['en' => 'replies', 'ar' => 'ردود', 'ja' => '返信'],
        
                'returning_user' => ['en' => 'returning user', 'ar' => 'عضو راجع تاني', 'ja' => '再訪ユーザー'],
        'login_headline' => ['en' => 'Back to the noise.', 'ar' => 'رجعت للدوشة.', 'ja' => 'また戻ってきたね。'],
        'login_intro' => ['en' => 'Log in to start topics, reply to threads, and edit your little corner of the board.', 'ar' => 'سجّل دخول عشان تبدأ مواضيع، ترد على الناس، وتظبط ركنك الصغير في المنتدى.', 'ja' => 'ログインしてトピックを立てたり、返信したり、自分のコーナーを編集しよう。'],
        'username_label' => ['en' => 'Username', 'ar' => 'اسم المستخدم', 'ja' => 'ユーザー名'],
        'password_label' => ['en' => 'Password', 'ar' => 'الباسورد', 'ja' => 'パスワード'],
        'human_check' => ['en' => 'Human check', 'ar' => 'تأكيد إنك مش روبوت', 'ja' => '人間確認'],
        'login_button' => ['en' => 'Log in', 'ar' => 'تسجيل الدخول', 'ja' => 'ログイン'],
        'forgot_password' => ['en' => 'Forgot your password?', 'ar' => 'نسيت الباسورد؟', 'ja' => 'パスワードを忘れた？'],
        'need_account' => ['en' => 'Need an account?', 'ar' => 'محتاج حساب؟', 'ja' => 'アカウントが必要？'],
        'login_aside_stamp' => ['en' => 'LOGIN SCREEN', 'ar' => 'شاشة الدخول', 'ja' => 'ログイン画面'],
        'login_aside_headline' => ['en' => 'One account. Four sections.', 'ar' => 'حساب واحد. أربع أقسام.', 'ja' => 'アカウントは一つ。セクションは四つ。'],
        'login_aside_body' => ['en' => 'General, Tech, Politics, and Anime are all waiting in the sidebar. Pick your poison.', 'ar' => 'عام، تكنولوجيا، سياسة، وأنمي كلهم مستنينك في القايمة الجانبية. اختار سمّك اللي يعجبك.', 'ja' => '一般、テック、政治、アニメがサイドバーで待ってるよ。好きなのを選んでね。'],
        'err_captcha' => ['en' => 'Solve the human check correctly.', 'ar' => 'حل تأكيد إنك مش روبوت صح.', 'ja' => '人間確認を正しく解いてね。'],
        'err_login' => ['en' => 'That username/password combination did not work.', 'ar' => 'اسم المستخدم أو الباسورد مش مظبوطين.', 'ja' => 'ユーザー名かパスワードが違うみたい。'],
        'flash_logged_in' => ['en' => 'You are logged in.', 'ar' => 'تم تسجيل دخولك.', 'ja' => 'ログインしました。'],
        
                'reg_eyebrow' => ['en' => 'new user registration', 'ar' => 'تسجيل عضو جديد', 'ja' => '新規ユーザー登録'],
        'reg_headline' => ['en' => 'Make yourself a username.', 'ar' => 'اختار لك اسم يليق بيك.', 'ja' => '自分だけのユーザー名を作ろう。'],
        'reg_intro' => ['en' => 'No email address. No tracking profile. Just a name, a password, and one secret word you will remember.', 'ar' => 'من غير إيميل. من غير بروفايل بيتبعك في كل حتة. بس اسم، وباسورد، وكلمة سر واحدة متنساهاش.', 'ja' => 'メールアドレス不要。追跡プロフィールもなし。名前とパスワード、そして忘れない秘密の単語がひとつあればOK。'],
        'password_hint' => ['en' => '8 characters minimum.', 'ar' => '8 حروف على الأقل، متكسلش.', 'ja' => '最低8文字、ちゃんと考えてね。'],
        'secret_word_label' => ['en' => 'Secret word', 'ar' => 'الكلمة السرية', 'ja' => '秘密の単語'],
        'secret_word_hint' => ['en' => 'Used only if you need to recover your password.', 'ar' => 'بنستخدمها بس لو نسيت الباسورد، فمتنساهاش هي كمان', 'ja' => 'パスワードを忘れたときだけ使うよ。これも忘れないでね（笑）'],
        'accept_terms_prefix' => ['en' => 'I accept the ', 'ar' => 'موافق على ', 'ja' => ''],
        'accept_terms_suffix' => ['en' => '.', 'ar' => '.', 'ja' => 'に同意する。'],
        'create_account_btn' => ['en' => 'Create my account', 'ar' => 'يلا نعمل الحساب', 'ja' => 'アカウントを作る'],
        'already_registered' => ['en' => 'Already registered? ', 'ar' => 'عندك حساب بالفعل؟ ', 'ja' => 'もう登録済み？ '],
        'login_here' => ['en' => 'Log in here.', 'ar' => 'ادخل من هنا.', 'ja' => 'こちらからログイン。'],
        'reg_aside_stamp' => ['en' => 'FIELD NOTES', 'ar' => 'الحكمة بتقولك', 'ja' => 'ことわざ'],
        'reg_aside_headline' => ['en' => 'Keep it human.', 'ar' => 'الكلام اللي يطلع من القلب يدخل القلب.', 'ja' => '言葉は心の使い。'],
        'reg_aside_body' => ['en' => 'RaspGrasp has no follower counts and no algorithmic feed. Find a thread, read the room, and add something worth replying to.', 'ar' => 'من غير متابعين، من غير خوارزمية. اتكلم من قلبك، واسمع بقلبك كمان.', 'ja' => 'フォロワー数もアルゴリズムもここにはない。心から話して、心で聞こう。'],
        'err_username_format' => ['en' => 'Usernames must be 3–32 characters using letters, numbers, or underscores.', 'ar' => 'اسم المستخدم لازم يكون من 3 لـ 32 حرف، إنجليزي وأرقام و_ بس.', 'ja' => 'ユーザー名は3〜32文字の英数字と「_」のみ使えます。'],
        'err_password_length' => ['en' => 'Passwords must be at least 8 characters.', 'ar' => 'الباسورد لازم يكون 8 حروف على الأقل.', 'ja' => 'パスワードは8文字以上にしてね。'],
        'err_secret_length' => ['en' => 'Your secret word must be at least 3 characters.', 'ar' => 'الكلمة السرية لازم تكون 3 حروف على الأقل.', 'ja' => '秘密の単語は3文字以上にしてね。'],
        'err_terms_required' => ['en' => 'You must accept the Terms of Use to create an account.', 'ar' => 'لازم توافق على شروط الاستخدام عشان تعمل حساب.', 'ja' => 'アカウントを作るには利用規約への同意が必要です。'],
        'err_username_taken' => ['en' => 'That username is already taken.', 'ar' => 'اسم المستخدم ده متاخد خلاص، جرب واحد تاني.', 'ja' => 'そのユーザー名はもう使われてるよ。別のを考えよう。'],
        'flash_account_created' => ['en' => 'Account created. Welcome to the board.', 'ar' => 'اتعمل الحساب. أهلاً بيك في المنتدى.', 'ja' => 'アカウント作成完了。掲示板へようこそ。'],
        
                'user_not_found' => ['en' => 'User not found', 'ar' => 'المستخدم مش موجود', 'ja' => 'ユーザーが見つかりません'],
        'no_user_named' => ['en' => 'There is no user by that name.', 'ar' => 'مفيش حد بالاسم ده.', 'ja' => 'その名前のユーザーはいません。'],
        'back_to_topics' => ['en' => 'Back to topics', 'ar' => 'رجوع للمواضيع', 'ja' => 'トピック一覧に戻る'],
        'member_profile' => ['en' => 'member profile', 'ar' => 'الملف الشخصي', 'ja' => 'メンバープロフィール'],
        'joined_prefix' => ['en' => 'joined ', 'ar' => 'انضم في ', 'ja' => '参加日：'],
        'edit_profile' => ['en' => 'edit profile', 'ar' => 'تعديل الملف', 'ja' => 'プロフィールを編集'],
        'about_label' => ['en' => 'ABOUT', 'ar' => 'نبذة', 'ja' => '自己紹介'],
        'no_bio_yet' => ['en' => 'This user has not written a bio yet.', 'ar' => 'المستخدم ده لسه مكتبش نبذة.', 'ja' => 'まだ自己紹介が書かれていません。'],
        'username_changed_prefix' => ['en' => 'username changed ', 'ar' => 'اتغيّر اسم المستخدم ', 'ja' => 'ユーザー名を'],
        'username_changed_suffix' => ['en' => ' times', 'ar' => ' مرات', 'ja' => '回変更済み'],
        'topics_started' => ['en' => 'Topics started', 'ar' => 'المواضيع اللي بدأها', 'ja' => '開始したトピック'],
        'shown_suffix' => ['en' => ' shown', 'ar' => ' معروضة', 'ja' => '件表示'],
        'no_topics_posted' => ['en' => 'No topics posted yet.', 'ar' => 'لسه مفيش مواضيع.', 'ja' => 'まだトピックがありません。'],
        
                'new_thread' => ['en' => 'new forum thread', 'ar' => 'موضوع جديد في المنتدى', 'ja' => '新規スレッド'],
        'create_post_headline' => ['en' => 'Put something on the board.', 'ar' => 'حط حاجة على اللوحة.', 'ja' => '何か掲示板に置いてみよう。'],
        'create_post_intro' => ['en' => 'Good topics are specific enough to answer and open enough to argue about.', 'ar' => 'أحسن المواضيع اللي بتكون محددة كفاية تتجاوب عليها، ومفتوحة كفاية يتناقش فيها.', 'ja' => 'いいトピックは、答えられるくらい具体的で、議論できるくらいオープンなもの。'],
        'section_label' => ['en' => 'Section', 'ar' => 'القسم', 'ja' => 'セクション'],
        'choose_section' => ['en' => 'Choose a section', 'ar' => 'اختار قسم', 'ja' => 'セクションを選択'],
        'topic_title_label' => ['en' => 'Topic title', 'ar' => 'عنوان الموضوع', 'ja' => 'トピックのタイトル'],
        'what_to_say' => ['en' => 'What do you want to say?', 'ar' => 'عايز تقول إيه؟', 'ja' => '何を伝えたい？'],
        'cancel' => ['en' => 'Cancel', 'ar' => 'إلغاء', 'ja' => 'キャンセル'],
        'post_topic_btn' => ['en' => 'Post topic', 'ar' => 'انشر الموضوع', 'ja' => 'トピックを投稿'],
        'err_title_length' => ['en' => 'Titles must be between 4 and 140 characters.', 'ar' => 'العنوان لازم يكون بين 4 و140 حرف.', 'ja' => 'タイトルは4〜140文字にしてね。'],
        'err_body_length' => ['en' => 'Give the topic a little more substance.', 'ar' => 'زوّد شوية في الكلام، الموضوع محتاج تفاصيل أكتر.', 'ja' => 'もう少し内容を書いてね。'],
        'err_invalid_category' => ['en' => 'Choose a valid section.', 'ar' => 'اختار قسم صحيح.', 'ja' => '有効なセクションを選んでね。'],
        'flash_topic_posted' => ['en' => 'Topic posted.', 'ar' => 'اتنشر الموضوع.', 'ja' => 'トピックを投稿しました。'],
                'back_to_latest' => ['en' => '← Back to latest topics', 'ar' => '← رجوع لأحدث المواضيع', 'ja' => '← 最新トピックに戻る'],
        'posted_by' => ['en' => 'posted by', 'ar' => 'كتبها', 'ja' => '投稿者：'],
        'reply_singular' => ['en' => 'reply', 'ar' => 'رد', 'ja' => '件の返信'],
        'reply_plural' => ['en' => 'replies', 'ar' => 'ردود', 'ja' => '件の返信'],
        'public_record' => ['en' => 'the public record', 'ar' => 'السجل العام', 'ja' => '公開記録'],
        'no_replies_yet' => ['en' => 'No replies yet. The first one is always a little awkward.', 'ar' => 'مفيش ردود لسه. أول رد دايمًا بيبقى محرج شوية.', 'ja' => 'まだ返信がありません。最初の一言はいつも少し気まずいもの。'],
        'spread_unlimited_btn' => ['en' => '📢 Spread this topic (unlimited)', 'ar' => '📢 انشر الموضوع (من غير حدود)', 'ja' => '📢 このトピックを拡散（無制限）'],
        'spread_left_btn' => ['en' => '📢 Spread this topic (%d left this week)', 'ar' => '📢 انشر الموضوع (باقيلك %d الأسبوع ده)', 'ja' => '📢 このトピックを拡散（今週あと%d回）'],
        'spread_used_up_btn' => ['en' => 'Weekly spreads used — resets next week', 'ar' => 'خلصت مرات الأسبوع — هترجع الأسبوع الجاي', 'ja' => '今週の拡散回数を使い切りました。来週リセットされます'],
        'your_reply_label' => ['en' => 'Your reply', 'ar' => 'ردك', 'ja' => 'あなたの返信'],
        'reply_placeholder' => ['en' => 'Add your two cents...', 'ar' => 'قول رأيك...', 'ja' => 'あなたの意見をどうぞ…'],
        'reply_to_topic_btn' => ['en' => 'Reply to topic', 'ar' => 'رد على الموضوع', 'ja' => 'トピックに返信'],
        'to_reply_suffix' => ['en' => ' to reply.', 'ar' => ' عشان ترد.', 'ja' => 'すると返信できます。'],
        'or_word' => ['en' => ' or ', 'ar' => ' أو ', 'ja' => 'か'],
        'topic_not_here' => ['en' => 'That topic is not here.', 'ar' => 'الموضوع ده مش موجود هنا.', 'ja' => 'そのトピックは見つかりません。'],
        'topic_removed_note' => ['en' => 'It may have been removed or the link may be wrong.', 'ar' => 'ممكن يكون اتشال أو اللينك غلط.', 'ja' => '削除されたか、リンクが間違っている可能性があります。'],
                'your_account' => ['en' => 'your account', 'ar' => 'حسابك', 'ja' => 'あなたのアカウント'],
        'edit_profile_headline' => ['en' => 'Make your profile yours.', 'ar' => 'خلي البروفايل بتاعك يشبهك.', 'ja' => 'プロフィールを自分らしく。'],
        'edit_profile_intro' => ['en' => 'Update your public username and bio. Username changes are permanent and limited to three.', 'ar' => 'عدّل اسم المستخدم والنبذة الظاهرة للعامة. تغيير الاسم نهائي ومحدود بـ 3 مرات بس.', 'ja' => '公開ユーザー名と自己紹介を更新しよう。ユーザー名の変更は永久的で、3回までに制限されています。'],
        'profile_picture_label' => ['en' => 'Profile picture', 'ar' => 'الصورة الشخصية', 'ja' => 'プロフィール写真'],
        'profile_picture_hint' => ['en' => 'JPEG, PNG, GIF, or WebP. Maximum 2 MB.', 'ar' => 'JPEG أو PNG أو GIF أو WebP. أقصى حجم 2 ميجابايت.', 'ja' => 'JPEG、PNG、GIF、WebPに対応。最大2MB。'],
        'username_change_plural' => ['en' => '%d username change(s) remaining.', 'ar' => 'باقيلك %d تغيير لاسم المستخدم.', 'ja' => 'ユーザー名の変更はあと%d回。'],
        'bio_label' => ['en' => 'Bio', 'ar' => 'النبذة', 'ja' => '自己紹介'],
        'bio_placeholder' => ['en' => 'Tell the forum a little about yourself.', 'ar' => 'قول للمنتدى نبذة بسيطة عنك.', 'ja' => '掲示板にちょっとした自己紹介を。'],
        'bio_max_hint' => ['en' => '255 characters maximum.', 'ar' => 'أقصى حد 255 حرف.', 'ja' => '最大255文字。'],
        'save_profile_btn' => ['en' => 'Save profile', 'ar' => 'احفظ البروفايل', 'ja' => 'プロフィールを保存'],
        'err_bio_length' => ['en' => 'Your bio must be 255 characters or fewer.', 'ar' => 'النبذة لازم تكون 255 حرف أو أقل.', 'ja' => '自己紹介は255文字以内にしてね。'],
        'err_username_changes_used' => ['en' => 'You have used all 3 permanent username changes.', 'ar' => 'خلّصت الـ 3 مرات بتوع تغيير اسم المستخدم.', 'ja' => 'ユーザー名の変更（3回まで）をすべて使い切りました。'],
        'err_avatar_upload' => ['en' => 'The profile picture could not be uploaded. Please try again.', 'ar' => 'مقدرناش نرفع الصورة. جرب تاني.', 'ja' => 'プロフィール写真をアップロードできませんでした。もう一度試してね。'],
        'err_avatar_size' => ['en' => 'Profile pictures must be 2 MB or smaller.', 'ar' => 'الصورة لازم تكون 2 ميجابايت أو أقل.', 'ja' => 'プロフィール写真は2MB以下にしてね。'],
        'err_avatar_invalid' => ['en' => 'The uploaded profile picture was not valid.', 'ar' => 'الصورة اللي رفعتها مش صالحة.', 'ja' => 'アップロードされた写真が無効でした。'],
        'err_avatar_type' => ['en' => 'Profile pictures must be valid JPEG, PNG, GIF, or WebP images.', 'ar' => 'الصورة لازم تكون JPEG أو PNG أو GIF أو WebP صحيحة.', 'ja' => 'プロフィール写真は有効なJPEG、PNG、GIF、WebP画像にしてね。'],
        'err_avatar_folder' => ['en' => 'The profile picture folder is not writable on this server.', 'ar' => 'مجلد الصور مش قابل للكتابة على السيرفر.', 'ja' => 'サーバー上のプロフィール写真フォルダに書き込めません。'],
        'err_avatar_save' => ['en' => 'The profile picture could not be saved. Please try again.', 'ar' => 'مقدرناش نحفظ الصورة. جرب تاني.', 'ja' => 'プロフィール写真を保存できませんでした。もう一度試してね。'],
        'flash_profile_updated' => ['en' => 'Your profile was updated.', 'ar' => 'اتعدّل البروفايل بتاعك.', 'ja' => 'プロフィールを更新しました。'],
        



        'account_recovery' => ['en' => 'account recovery', 'ar' => 'استرجاع الحساب', 'ja' => 'アカウント復旧'],
        'forgot_headline' => ['en' => 'Forgot the password?', 'ar' => 'نسيت الباسورد يفالح؟', 'ja' => 'パスワードを忘れた？'],
        'forgot_intro' => ['en' => 'Use the secret word you chose when you joined. There is no email step here.', 'ar' => 'استخدم الكلمة السرية اللي اخترتها وانت بتسجل.', 'ja' => '登録時に選んだ秘密の単語を使ってね。ここにメールの手順はありません。'],
        'err_recovery_mismatch' => ['en' => 'The username and secret word did not match.', 'ar' => 'اسم المستخدم والكلمة السرية مش متطابقين.', 'ja' => 'ユーザー名と秘密の単語が一致しませんでした。'],
        'err_new_password_length' => ['en' => 'New passwords must be at least 8 characters.', 'ar' => 'الباسورد الجديد لازم يكون 8 حروف على الأقل.', 'ja' => '新しいパスワードは8文字以上にしてね。'],
        'success_password_updated' => ['en' => 'Password updated. You can log in now.', 'ar' => 'اتغيّر الباسورد. تقدر تسجّل دخول دلوقتي.', 'ja' => 'パスワードを更新しました。今すぐログインできます。'],
        'new_password_label' => ['en' => 'New password', 'ar' => 'الباسورد الجديد', 'ja' => '新しいパスワード'],
        'set_new_password_btn' => ['en' => 'Set new password', 'ar' => 'احفظ الباسورد الجديد', 'ja' => '新しいパスワードを設定'],
        'return_to_login' => ['en' => 'Return to login.', 'ar' => 'ارجع لتسجيل الدخول.', 'ja' => 'ログインに戻る。'],
        'no_email_required' => ['en' => 'NO EMAIL REQUIRED', 'ar' => 'من غير إيميل خالص', 'ja' => 'メール不要'],
        'old_school_recovery' => ['en' => 'Old-school recovery.', 'ar' => 'استرجاع بالطريقة القديمة.', 'ja' => '昔ながらの復旧方法。'],
        'recovery_aside_body' => ['en' => 'Keep your secret word somewhere safe. RaspGrasp cannot send a reset link because it never asked for your inbox.', 'ar' => 'احفظ الكلمة السرية بتاعتك في مكان آمن لان عطية مش هيقدر يبعتلك لينك استرجاع لأنه أصلاً معندوش إيميلك.', 'ja' => '秘密の単語は大切に保管してね。RaspGraspはメールアドレスを聞いたことがないので、リセットリンクを送ることはできません。'],
    ];
    return $strings[$key][$lang] ?? $strings[$key]['en'] ?? $key;
}

function terms_text(string $lang): array
{
    $content = [
        'en' => [
            'eyebrow' => 'site terms', 'title' => 'Terms of Use', 'updated' => 'Last updated: September 16, 2026',
            'sections' => [
                ['h' => '1. Using RaspGrasp', 'p' => 'RaspGrasp is a community forum operated by the RaspGrasp project on GitHub. By creating an account or using the forum, you agree to follow these terms and the law that applies where you live.'],
                ['h' => '2. Accounts', 'p' => 'You are responsible for keeping your username, password, and secret word private. Do not impersonate another person, share access to an account, or create accounts to evade a restriction. RaspGrasp may limit or remove accounts that abuse the service.'],
                ['h' => '3. Your content', 'p' => 'You keep ownership of content you post. You grant RaspGrasp permission to store, display, and technically reproduce that content as needed to operate the forum. Do not post content that you do not have the right to share.'],
                ['h' => '4. Mature and adult content', 'p' => 'RaspGrasp allows adult (18+) content, including explicit material, as long as everyone shown or described is a real, consenting adult, or is clearly fictional. This is an adult space and content is not filtered for a general audience.'],
                ['h' => '5. Free expression', 'p' => 'RaspGrasp allows blunt, offensive, and politically extreme speech, including opinions many people would find distasteful or wrong. Disagreement, insults, dark humor, and unpopular or fringe political positions are not, by themselves, against these terms. Allowing a view to be posted is not an endorsement of it by RaspGrasp.'],
                ['h' => '6. Absolutely prohibited', 'p' => 'The following are never allowed on RaspGrasp, with no exceptions:', 'items' => [
                    'Any sexual content involving minors, in any form — real, simulated, fictional, or drawn/AI-generated. Accounts posting this are permanently banned immediately and, where required by law, reported to the relevant authorities.',
                    'Malware, exploits, phishing, credential theft, or other malicious code or links.',
                    'Technical spam: automated posting, mass unsolicited advertising, or abuse of the platform\'s infrastructure.',
                    'Real doxxing: publishing another real person\'s private information (home address, workplace, phone number, government ID, financial details, or similar) without their consent, with intent to expose, harass, or endanger them.',
                ]],
                ['h' => '7. Moderation and availability', 'p' => 'The project may remove content, suspend accounts, or restrict access when necessary to enforce Section 6 or protect the service. Staff and the owner may promote a topic using the Spread feature under the limits set for their role. RaspGrasp is provided as-is and may change, be interrupted, or be discontinued.'],
                ['h' => '8. Password recovery', 'p' => 'Password recovery uses the secret word chosen at registration. Treat it like a second password. RaspGrasp cannot guarantee recovery if you lose both your password and secret word.'],
                ['h' => '9. Changes', 'p' => 'These terms may be updated as the project changes. Continued use after an update means you accept the revised terms. The registration page records when an account accepted the terms.'],
                ['h' => '10. Project owner', 'p' => 'Project source and ownership information are available through the RaspGrasp GitHub account.'],
            ],
        ],
        'ar' => [
            'eyebrow' => 'شروط الموقع', 'title' => 'شروط الاستخدام', 'updated' => 'آخر تحديث: 16 سبتمبر 2026',
            'sections' => [
                ['h' => '1. استخدام راسب جراسب', 'p' => 'راسب جراسب منتدى مجتمعي يديره مشروع RaspGrasp على GitHub. بإنشائك حسابًا أو استخدامك للمنتدى، فإنك توافق على الالتزام بهذه الشروط والقوانين المعمول بها في بلدك.'],
                ['h' => '2. الحسابات', 'p' => 'أنت مسؤول عن الحفاظ على سرية اسم المستخدم وكلمة المرور والكلمة السرية الخاصة بك. يُمنع انتحال شخصية آخر، أو مشاركة الوصول إلى حساب، أو إنشاء حسابات للتحايل على أي قيد. يجوز لراسب جراسب تقييد أو إزالة الحسابات التي تسيء استخدام الخدمة.'],
                ['h' => '3. المحتوى الخاص بك', 'p' => 'تحتفظ بملكية المحتوى الذي تنشره. أنت تمنح راسب جراسب إذنًا بتخزين وعرض واستنساخ هذا المحتوى تقنيًا بالقدر اللازم لتشغيل المنتدى. لا تنشر محتوى ليس لديك الحق في مشاركته.'],
                ['h' => '4. المحتوى للبالغين', 'p' => 'يسمح راسب جراسب بمحتوى للبالغين (+18)، بما في ذلك المحتوى الصريح، طالما أن كل شخص يظهر أو يُوصف فيه بالغ حقيقي وموافق، أو أن المحتوى خيالي بوضوح. هذه مساحة مخصّصة للبالغين والمحتوى غير مُصفّى لجمهور عام.'],
                ['h' => '5. حرية التعبير', 'p' => 'يسمح راسب جراسب بالكلام الصريح والمسيء والآراء السياسية المتطرفة، بما في ذلك آراء قد يراها كثيرون غير لائقة أو خاطئة. الاختلاف والإهانات والفكاهة السوداء والمواقف السياسية غير الشائعة أو الهامشية ليست، في حد ذاتها، مخالفة لهذه الشروط. السماح بنشر رأي ما لا يعني تأييد راسب جراسب له.'],
                ['h' => '6. ممنوع منعًا باتًا', 'p' => 'الآتي ممنوع تمامًا على راسب جراسب، بلا استثناءات:', 'items' => [
                    'أي محتوى جنسي يتعلق بالقاصرين، بأي شكل — حقيقي، أو محاكى، أو خيالي، أو مرسوم/مولّد بالذكاء الاصطناعي. الحسابات التي تنشر هذا المحتوى تُحظر نهائيًا وفورًا، ويُبلَّغ عنها للجهات المختصة إذا اقتضى القانون ذلك.',
                    'البرمجيات الخبيثة، والثغرات، والتصيّد الاحتيالي، وسرقة بيانات الدخول، أو أي كود أو روابط ضارة أخرى.',
                    'السبام التقني: النشر الآلي، أو الإعلانات الجماعية غير المرغوبة، أو إساءة استخدام البنية التحتية للمنصة.',
                    'الدوكسنج الحقيقي: نشر معلومات خاصة بشخص حقيقي آخر (عنوان المنزل، مكان العمل، رقم الهاتف، الهوية الحكومية، البيانات المالية، أو ما شابه) دون موافقته، بنية كشفه أو مضايقته أو تعريضه للخطر.',
                ]],
                ['h' => '7. الإشراف والتوفر', 'p' => 'يجوز للمشروع إزالة محتوى، أو تعليق حسابات، أو تقييد الوصول عند الضرورة لتطبيق البند 6 أو حماية الخدمة. يمكن لفريق الإشراف والمالك الترويج لموضوع باستخدام ميزة Spread ضمن الحدود المقررة لدورهم. يُقدَّم راسب جراسب كما هو، وقد يتغير أو يتوقف أو ينقطع.'],
                ['h' => '8. استعادة كلمة المرور', 'p' => 'تعتمد استعادة كلمة المرور على الكلمة السرية المختارة عند التسجيل. تعامل معها كأنها كلمة مرور ثانية. لا يضمن راسب جراسب استعادة الحساب إذا فقدت كلمة المرور والكلمة السرية معًا.'],
                ['h' => '9. التعديلات', 'p' => 'قد يتم تحديث هذه الشروط مع تطور المشروع. استمرارك في الاستخدام بعد أي تحديث يعني موافقتك على الشروط المعدّلة. تسجّل صفحة إنشاء الحساب تاريخ موافقة الحساب على الشروط.'],
                ['h' => '10. مالك المشروع', 'p' => 'معلومات مصدر المشروع وملكيته متاحة عبر حساب راسب جراسب على GitHub.'],
            ],
        ],
        'ja' => [
            'eyebrow' => 'サイト規約', 'title' => '利用規約', 'updated' => '最終更新日：2026年9月16日',
            'sections' => [
                ['h' => '1. RaspGraspの利用', 'p' => 'RaspGraspは、GitHub上のRaspGraspプロジェクトが運営するコミュニティフォーラムです。アカウントを作成またはフォーラムを利用することで、本規約およびお住まいの地域の法律に従うことに同意したものとみなされます。'],
                ['h' => '2. アカウント', 'p' => 'ユーザー名、パスワード、秘密の単語はご自身で管理してください。他人になりすましたり、アカウントへのアクセスを共有したり、制限を回避する目的でアカウントを作成したりすることは禁止です。サービスを悪用するアカウントは、RaspGraspにより制限または削除される場合があります。'],
                ['h' => '3. あなたのコンテンツ', 'p' => '投稿したコンテンツの所有権はあなたに帰属します。フォーラムの運営に必要な範囲で、RaspGraspがそのコンテンツを保存・表示・技術的に複製することを許可するものとします。共有する権利のないコンテンツは投稿しないでください。'],
                ['h' => '4. 成人向けコンテンツ', 'p' => '登場・描写される人物が実在し同意のある成人である場合、または明確に架空のものである場合に限り、RaspGraspは性的に露骨な表現を含む成人向け（18歳以上）コンテンツを許可します。ここは成人向けの空間であり、一般向けにフィルタリングされていません。'],
                ['h' => '5. 表現の自由', 'p' => 'RaspGraspは、多くの人が不快または誤りだと感じるであろう意見も含め、率直で攻撃的、政治的に過激な発言を許可します。反対意見、侮辱、ブラックユーモア、少数派・過激な政治的立場は、それ自体では本規約違反にはなりません。ある意見の投稿を許可することは、RaspGraspによるその意見の支持を意味しません。'],
                ['h' => '6. 絶対禁止事項', 'p' => '以下は、例外なくRaspGrasp上で常に禁止されています：', 'items' => [
                    '未成年者が関わるあらゆる性的コンテンツ（実在、シミュレーション、架空、描画・AI生成を問わず）。これを投稿したアカウントは即座に永久停止となり、法律が求める場合は関係当局に通報されます。',
                    'マルウェア、エクスプロイト、フィッシング、認証情報の窃取、その他の悪意あるコードやリンク。',
                    '技術的スパム：自動投稿、大量の迷惑広告、プラットフォームのインフラの悪用。',
                    '実際のドクシング：本人の同意なく、実在する他人の個人情報（自宅住所、勤務先、電話番号、公的身分証明、金融情報など）を、暴露・嫌がらせ・危害の意図で公開すること。',
                ]],
                ['h' => '7. モデレーションと提供状況', 'p' => '本プロジェクトは、第6項の遵守またはサービス保護のために必要な場合、コンテンツの削除、アカウントの停止、アクセスの制限を行うことがあります。スタッフおよびオーナーは、それぞれの役割に定められた範囲内でSpread機能を使いトピックを宣伝できます。RaspGraspは現状のまま提供され、変更・中断・終了する場合があります。'],
                ['h' => '8. パスワードの復旧', 'p' => 'パスワードの復旧には登録時に設定した秘密の単語を使用します。これは第二のパスワードとして扱ってください。パスワードと秘密の単語の両方を紛失した場合、RaspGraspは復旧を保証できません。'],
                ['h' => '9. 変更', 'p' => '本規約はプロジェクトの発展に伴い更新される場合があります。更新後も利用を継続する場合、改定後の規約に同意したものとみなされます。登録ページには、アカウントが規約に同意した日時が記録されます。'],
                ['h' => '10. プロジェクトオーナー', 'p' => 'プロジェクトのソースおよび所有権に関する情報は、RaspGraspのGitHubアカウントから確認できます。'],
                
                
                        'pt_login' => ['en' => 'Log in', 'ar' => 'تسجيل الدخول', 'ja' => 'ログイン'],
        'pt_create_account' => ['en' => 'Create account', 'ar' => 'إنشاء حساب', 'ja' => 'アカウント作成'],
        'pt_topic_not_found' => ['en' => 'Topic not found', 'ar' => 'الموضوع مش موجود', 'ja' => 'トピックが見つかりません'],
        'pt_new_topic' => ['en' => 'Start a topic', 'ar' => 'ابدأ موضوع', 'ja' => 'トピックを作成'],
        'pt_forgot_password' => ['en' => 'Forgot password', 'ar' => 'نسيت الباسورد', 'ja' => 'パスワードを忘れた'],
        'pt_edit_profile' => ['en' => 'Edit profile', 'ar' => 'تعديل الملف الشخصي', 'ja' => 'プロフィール編集'],
                
                
            ],
        ],
    ];
    return $content[$lang] ?? $content['en'];
}

function localized_date(string $datetime, string $lang): string
{
    $ts = strtotime($datetime);
    if ($lang === 'ar') {
        $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        return (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . '، ' . date('Y', $ts);
    }
    if ($lang === 'ja') {
        return date('Y', $ts) . '年' . (int)date('n', $ts) . '月' . (int)date('j', $ts) . '日';
    }
    return date('M j, Y', $ts);
}

function breadcrumb_title(string $pageTitle, string $lang): string
{
    $map = [
        'Latest topics' => 'latest_topics', 'Log in' => 'pt_login', 'Create account' => 'pt_create_account',
        'Topic not found' => 'pt_topic_not_found', 'Start a topic' => 'pt_new_topic',
        'Forgot password' => 'pt_forgot_password', 'Edit profile' => 'pt_edit_profile',
        'Terms of Use' => 'terms_of_use', 'User not found' => 'user_not_found',
    ];
    return isset($map[$pageTitle]) ? t($map[$pageTitle]) : $pageTitle;
}

function localized_time_ago(string $datetime, string $lang): string
{
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 60) {
        return ['en' => 'just now', 'ar' => 'دلوقتي', 'ja' => 'たった今'][$lang] ?? 'just now';
    }
    $units = [
        31536000 => ['en' => ['y', 'y'], 'ar' => ['سنة', 'سنين'], 'ja' => '年'],
        2592000  => ['en' => ['mo', 'mo'], 'ar' => ['شهر', 'شهور'], 'ja' => 'ヶ月'],
        604800   => ['en' => ['w', 'w'], 'ar' => ['أسبوع', 'أسابيع'], 'ja' => '週間'],
        86400    => ['en' => ['d', 'd'], 'ar' => ['يوم', 'أيام'], 'ja' => '日'],
        3600     => ['en' => ['h', 'h'], 'ar' => ['ساعة', 'ساعات'], 'ja' => '時間'],
        60       => ['en' => ['m', 'm'], 'ar' => ['دقيقة', 'دقايق'], 'ja' => '分'],
    ];
    foreach ($units as $secs => $labels) {
        if ($diff >= $secs) {
            $n = (int)floor($diff / $secs);
            if ($lang === 'ja') return $n . $labels['ja'] . '前';
            if ($lang === 'ar') return 'من ' . $n . ' ' . ($n === 1 ? $labels['ar'][0] : $labels['ar'][1]);
            return $n . $labels['en'][0] . ' ago';
        }
    }
    return '';
}

function category_description(string $lang, string $slug, string $fallback): string
{
    $map = [
        'ar' => [
            'general' => 'اللوبي الرئيسي لأي حاجة تانية.',
            'tech' => 'كمبيوترات، كود، أجهزة، وحاجات بتزن.',
            'politics' => 'نقاشات، أفكار، والحياة العامة.',
            'anime' => 'مسلسلات، مانجا، فن، وكلام المعجبين.',
        ],
        'ja' => [
            'general' => 'その他何でもありのメインロビー。',
            'tech' => 'パソコン、コード、ガジェット、そしてピーピー鳴るもの。',
            'politics' => '議論、アイデア、そして公共生活。',
            'anime' => 'アニメ、漫画、アート、そしてファンの話。',
        ],
    ];
    return $map[$lang][$slug] ?? $fallback;
}