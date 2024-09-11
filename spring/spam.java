@RequestMapping(value = "/comment/add", method = RequestMethod.POST)
    public ResponsePSLP<Map<String, Object>> comment_add(
            HttpServletRequest request,
            @RequestBody Comment comment
    ) throws Throwable {

        Map<String, Object> result = new HashMap<>();

        TraceWriter traceWriter = new TraceWriter("", request.getMethod(), request.getServletPath());

        try {
            MemberSession session = MemberSessionHelper.getSession(request);

            if(session == null || StringUtil.isEmpty(session.getMemberId())) {
                throw new PSLPException(PSLPException.Code.UserCertificationsError, "유효하지 않은 접근입니다!");
            }

            /*
            | ----------------------------------------------------------------------------------------
            | SPAM 단어 체크
            | ----------------------------------------------------------------------------------------
            */

            Spam spam = new Spam();
            spam = spamService.selectSpam(spam);

            // spamWord 목록과 비교
            if (spam != null && spam.getSpamWordList() != null && !spam.getSpamWordList().isEmpty()) {
                for (String spamWord : spam.getSpamWordList()) {
                    if (comment.getComment() != null && comment.getComment().contains(spamWord)) {
                        throw new PSLPException(PSLPException.Code.UserCertificationsError, "입력된 내용에 금지된 단어가 포함되어 있습니다: " + spamWord);
                    }
                }
            }

            traceWriter.add("* [serviceTp] : [" + comment.getServiceTp() + "]");
            traceWriter.add("* [serviceSeq] : [" + comment.getServiceSeq() + "]");
            traceWriter.add("* [comment] : [" + comment.getComment() + "]");

            comment.setMemberId(session.getMemberId());
            comment.setCreIp(getRealIpAddress(request));
            SimpleDateFormat newDtFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm");
            comment.setRegDate(newDtFormat.format(new Date()));
            int inserted = commentService.insertComment(comment);
            if(inserted > 0) {
                result.put("comment", comment);
            }

            result.put("status", inserted > 0);
        }
        // ERROR -> Exception
        catch (Exception e) {
            // ERROR -> ThrowableExceptions
            ThrowableExceptions(traceWriter, e);
        }
        finally {
            traceWriter.log(0);
        }
        return new ResponsePSLP<>(result);
    }